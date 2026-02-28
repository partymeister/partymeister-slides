import { setActivePinia, createPinia } from 'pinia'
import { usePlaylistStore } from '@/stores/playlistStore'
import { usePlaylistEngine, type StatusCallback } from '@/composables/usePlaylistEngine'
import { createPlaylist, resetPlaylistIdCounter } from '../../fixtures/playlists'
import { createPlaylistItem, resetItemIdCounter } from '../../fixtures/items'
import type { PlaylistItem } from '@/types/playlist'

// ── Mock dependencies ───────────────────────────────────────────────

function createMockStorage() {
  return {
    save: vi.fn().mockResolvedValue(undefined),
    load: vi.fn().mockResolvedValue(null),
    remove: vi.fn().mockResolvedValue(undefined),
    clear: vi.fn().mockResolvedValue(undefined),
  }
}

function createMockMidi() {
  return {
    enable: vi.fn().mockResolvedValue(true),
    playNote: vi.fn(),
    sendStopSignal: vi.fn(),
    isAvailable: { value: true },
    outputName: { value: 'Mock MIDI' },
    outputs: { value: [{}] },
  }
}

function createMockCables() {
  return {
    isReady: { value: true },
    setScene: vi.fn(),
    setSlideType: vi.fn(),
    setCompetitionName: vi.fn(),
    setSlideTypeString: vi.fn(),
    setEntryType: vi.fn(),
    extractCompetitionName: vi.fn().mockReturnValue(null),
    updateForSlideType: vi.fn(),
    resetBackground: vi.fn(),
  }
}

// ── Helpers ─────────────────────────────────────────────────────────

function setupEngine(opts: {
  itemCount?: number
  itemOverrides?: Partial<PlaylistItem>
  playlistOverrides?: Record<string, any>
  statusCallback?: StatusCallback
} = {}) {
  const { itemCount = 3, itemOverrides = {}, playlistOverrides = {}, statusCallback } = opts

  const store = usePlaylistStore()
  const storage = createMockStorage()
  const midi = createMockMidi()
  const cables = createMockCables()

  const items: PlaylistItem[] = []
  for (let i = 0; i < itemCount; i++) {
    items.push(createPlaylistItem(itemOverrides))
  }

  const playlist = createPlaylist({ items, ...playlistOverrides })
  store.cachePlaylist(playlist)
  store.setActivePlaylist(playlist.id)
  store.currentItemIndex = 0

  const engine = usePlaylistEngine(
    store,
    storage as any,
    midi as any,
    cables as any,
    statusCallback,
  )

  return { store, storage, midi, cables, engine, playlist, items }
}

// ── Tests ───────────────────────────────────────────────────────────

describe('usePlaylistEngine', () => {
  beforeEach(() => {
    vi.useFakeTimers()
    setActivePinia(createPinia())
    resetPlaylistIdCounter()
    resetItemIdCounter()
  })

  afterEach(() => {
    vi.useRealTimers()
  })

  // ── Navigation: seekToNext ──────────────────────────────────────

  describe('seekToNext', () => {
    it('should advance currentItemIndex by 1', () => {
      const { store, engine } = setupEngine()
      store.currentItemIndex = 0

      engine.seekToNext(true)

      expect(store.currentItemIndex).toBe(1)
    })

    it('should wrap around to 0 at the end of playlist', () => {
      const { store, engine } = setupEngine({ itemCount: 3 })
      store.currentItemIndex = 2

      engine.seekToNext(true)

      expect(store.currentItemIndex).toBe(0)
    })

    it('should clear existing timeouts before seeking', () => {
      const { store, engine } = setupEngine({
        itemOverrides: { duration: 30, is_advanced_manually: true },
      })
      store.currentItemIndex = 0

      // Manually set a slide timeout (duration=30s)
      // Override is_advanced_manually for this particular call
      store.items[0].is_advanced_manually = false
      engine.setSlideTimeout()
      // Restore so future afterSeek calls won't set new timeouts
      store.items[0].is_advanced_manually = true

      // Now seek - should clear the old timeout
      engine.seekToNext(true)
      // store is now at index 1

      // Advance past the old 30s timeout
      vi.advanceTimersByTime(35000)

      // If the old timeout was NOT cleared, it would have called seekToNext
      // and moved the index. Since is_advanced_manually=true on the new afterSeek,
      // no new timeout was set, so index should stay at 1.
      expect(store.currentItemIndex).toBe(1)
    })

    it('should use savedItemIndex for calculation when in playNow mode', () => {
      const { store, engine } = setupEngine({ itemCount: 5 })
      store.currentItemIndex = 0

      const playNowItem = createPlaylistItem()
      store.enterPlayNow(playNowItem)
      // savedItemIndex is now 0, so next should be 1
      engine.seekToNext(true)

      expect(store.currentItemIndex).toBe(1)
    })
  })

  // ── Navigation: seekToPrevious ──────────────────────────────────

  describe('seekToPrevious', () => {
    it('should decrement currentItemIndex by 1', () => {
      const { store, engine } = setupEngine()
      store.currentItemIndex = 2

      engine.seekToPrevious(true)

      expect(store.currentItemIndex).toBe(1)
    })

    it('should wrap around to last item from index 0', () => {
      const { store, engine } = setupEngine({ itemCount: 3 })
      store.currentItemIndex = 0

      engine.seekToPrevious(true)

      expect(store.currentItemIndex).toBe(2)
    })
  })

  // ── Navigation: seekToIndex ─────────────────────────────────────

  describe('seekToIndex', () => {
    it('should set currentItemIndex to the given index', () => {
      const { store, engine } = setupEngine({ itemCount: 5 })

      engine.seekToIndex(3)

      expect(store.currentItemIndex).toBe(3)
    })

    it('should do nothing for out-of-bounds index', () => {
      const { store, engine } = setupEngine({ itemCount: 3 })
      store.currentItemIndex = 1

      engine.seekToIndex(10)

      expect(store.currentItemIndex).toBe(1)
    })

    it('should do nothing for negative index', () => {
      const { store, engine } = setupEngine({ itemCount: 3 })
      store.currentItemIndex = 1

      engine.seekToIndex(-1)

      expect(store.currentItemIndex).toBe(1)
    })

    it('should always use hard transition', () => {
      const { store, storage, cables, engine } = setupEngine()

      engine.seekToIndex(2)

      // Hard transition: cables.updateForSlideType is called
      expect(cables.updateForSlideType).toHaveBeenCalled()
      // And afterSeek is called immediately (storage.save proves it)
      expect(storage.save).toHaveBeenCalledWith('currentItem', 2)
    })
  })

  // ── Transitions ─────────────────────────────────────────────────

  describe('prepareTransition', () => {
    it('should call afterSeek immediately for hard transitions', () => {
      const { store, storage, engine } = setupEngine()
      store.currentItemIndex = 1

      engine.prepareTransition(true)

      // afterSeek persists to storage
      expect(storage.save).toHaveBeenCalledWith('currentItem', 1)
    })

    it('should delay 250ms then set transition ready for soft transitions', () => {
      const { engine, storage } = setupEngine()

      engine.prepareTransition(false)

      // Not yet - 250ms has not elapsed
      expect(engine.isTransitioning.value).toBe(true)
      expect(storage.save).not.toHaveBeenCalled()

      vi.advanceTimersByTime(250)

      // After 250ms the animation should be ready, but afterSeek is NOT called
      // until onTransitionComplete is called by the component
      expect(storage.save).not.toHaveBeenCalled()
    })

    it('should set isTransitioning to true during soft transition', () => {
      const { engine } = setupEngine()

      engine.prepareTransition(false)

      expect(engine.isTransitioning.value).toBe(true)
    })

    it('should set isTransitioning to false after transition completes', () => {
      const { engine } = setupEngine()

      engine.prepareTransition(false)
      expect(engine.isTransitioning.value).toBe(true)

      vi.advanceTimersByTime(250)
      engine.onTransitionComplete()

      expect(engine.isTransitioning.value).toBe(false)
    })
  })

  // ── Timers: setSlideTimeout ─────────────────────────────────────

  describe('setSlideTimeout', () => {
    it('should set timeout for duration * 1000ms', () => {
      const { store, engine } = setupEngine({
        itemOverrides: { duration: 5, is_advanced_manually: false },
      })
      store.currentItemIndex = 0
      const initialIndex = store.currentItemIndex

      engine.setSlideTimeout()

      // Not yet fired
      vi.advanceTimersByTime(4999)
      expect(store.currentItemIndex).toBe(initialIndex)

      // Fires at 5000ms
      vi.advanceTimersByTime(1)
      expect(store.currentItemIndex).not.toBe(initialIndex)
    })

    it('should call seekToNext when timeout fires', () => {
      const { store, engine } = setupEngine({
        itemOverrides: { duration: 10, is_advanced_manually: false },
      })
      store.currentItemIndex = 0

      engine.setSlideTimeout()
      vi.advanceTimersByTime(10000)

      expect(store.currentItemIndex).toBe(1)
    })

    it('should not set timeout when is_advanced_manually is true', () => {
      const { store, engine } = setupEngine({
        itemOverrides: { is_advanced_manually: true, duration: 5 },
      })
      store.currentItemIndex = 0

      engine.setSlideTimeout()
      vi.advanceTimersByTime(10000)

      expect(store.currentItemIndex).toBe(0)
    })

    it('should not set timeout when in playNow mode', () => {
      const { store, engine } = setupEngine({
        itemOverrides: { duration: 5, is_advanced_manually: false },
      })
      store.currentItemIndex = 0

      const playNowItem = createPlaylistItem()
      store.enterPlayNow(playNowItem)

      engine.setSlideTimeout()
      vi.advanceTimersByTime(10000)

      // currentItemIndex should not have changed due to timeout
      expect(store.currentItemIndex).toBe(0)
    })
  })

  // ── Timers: setCallbackDelay ────────────────────────────────────

  describe('setCallbackDelay', () => {
    it('should fire GET to callback_url + callback_hash after delay', async () => {
      const fetchSpy = vi.fn().mockResolvedValue(new Response())
      vi.stubGlobal('fetch', fetchSpy)

      const { store, engine, playlist } = setupEngine({
        itemOverrides: { callback_hash: 'abc123', callback_delay: 2 },
        playlistOverrides: { callbacks: true, callback_url: 'http://localhost/cb/' },
      })
      store.currentItemIndex = 0

      engine.setCallbackDelay()

      expect(fetchSpy).not.toHaveBeenCalled()

      vi.advanceTimersByTime(2000)
      // Allow microtask queue to flush
      await vi.advanceTimersByTimeAsync(0)

      expect(fetchSpy).toHaveBeenCalledWith('http://localhost/cb/abc123')

      vi.unstubAllGlobals()
    })

    it('should not fire when callbacks is false', () => {
      const fetchSpy = vi.fn().mockResolvedValue(new Response())
      vi.stubGlobal('fetch', fetchSpy)

      const { engine } = setupEngine({
        itemOverrides: { callback_hash: 'abc123', callback_delay: 1 },
        playlistOverrides: { callbacks: false },
      })

      engine.setCallbackDelay()
      vi.advanceTimersByTime(5000)

      expect(fetchSpy).not.toHaveBeenCalled()

      vi.unstubAllGlobals()
    })

    it('should not fire when callback_hash is empty', () => {
      const fetchSpy = vi.fn().mockResolvedValue(new Response())
      vi.stubGlobal('fetch', fetchSpy)

      const { engine } = setupEngine({
        itemOverrides: { callback_hash: '', callback_delay: 1 },
        playlistOverrides: { callbacks: true },
      })

      engine.setCallbackDelay()
      vi.advanceTimersByTime(5000)

      expect(fetchSpy).not.toHaveBeenCalled()

      vi.unstubAllGlobals()
    })
  })

  // ── Timers: clearTimeouts ───────────────────────────────────────

  describe('clearTimeouts', () => {
    it('should clear both slide and callback timeouts', () => {
      const fetchSpy = vi.fn().mockResolvedValue(new Response())
      vi.stubGlobal('fetch', fetchSpy)

      const { store, engine } = setupEngine({
        itemOverrides: {
          duration: 10,
          is_advanced_manually: false,
          callback_hash: 'test',
          callback_delay: 5,
        },
        playlistOverrides: { callbacks: true, callback_url: 'http://localhost/cb/' },
      })
      store.currentItemIndex = 0

      engine.setSlideTimeout()
      engine.setCallbackDelay()

      engine.clearTimeouts()

      vi.advanceTimersByTime(20000)

      // Slide timeout would have changed the index
      expect(store.currentItemIndex).toBe(0)
      // Callback would have fired fetch
      expect(fetchSpy).not.toHaveBeenCalled()

      vi.unstubAllGlobals()
    })
  })

  // ── AfterSeek ───────────────────────────────────────────────────

  describe('afterSeek', () => {
    it('should persist currentItemIndex to storage', async () => {
      const { store, storage, engine } = setupEngine()
      store.currentItemIndex = 2

      await engine.afterSeek()

      expect(storage.save).toHaveBeenCalledWith('currentItem', 2)
    })

    it('should call midi.playNote with item midi_note', async () => {
      const { store, midi, engine } = setupEngine({
        itemOverrides: { midi_note: 42 },
      })
      store.currentItemIndex = 0

      await engine.afterSeek()

      expect(midi.playNote).toHaveBeenCalledWith(42)
    })

    it('should not play midi when midi_note is 0', async () => {
      const { store, midi, engine } = setupEngine({
        itemOverrides: { midi_note: 0 },
      })
      store.currentItemIndex = 0

      await engine.afterSeek()

      expect(midi.playNote).not.toHaveBeenCalled()
    })

    it('should call setSlideTimeout', async () => {
      const { store, engine } = setupEngine({
        itemOverrides: { duration: 7, is_advanced_manually: false },
      })
      store.currentItemIndex = 0

      await engine.afterSeek()

      // The timeout was set; advancing should trigger seekToNext
      vi.advanceTimersByTime(7000)
      expect(store.currentItemIndex).toBe(1)
    })

    it('should call setCallbackDelay', async () => {
      const fetchSpy = vi.fn().mockResolvedValue(new Response())
      vi.stubGlobal('fetch', fetchSpy)

      const { store, engine } = setupEngine({
        itemOverrides: { callback_hash: 'xyz', callback_delay: 3 },
        playlistOverrides: { callbacks: true, callback_url: 'http://localhost/cb/' },
      })
      store.currentItemIndex = 0

      await engine.afterSeek()

      vi.advanceTimersByTime(3000)
      await vi.advanceTimersByTimeAsync(0)

      expect(fetchSpy).toHaveBeenCalledWith('http://localhost/cb/xyz')

      vi.unstubAllGlobals()
    })
  })

  // ── PlayNow ─────────────────────────────────────────────────────

  describe('seekToPlayNow', () => {
    it('should enter playNow mode and transition to playNow item', () => {
      const { store, cables, engine } = setupEngine()

      const playNowItem = createPlaylistItem({ id: 99 })
      engine.seekToPlayNow(playNowItem)

      expect(store.playNow).toBe(true)
      expect(store.playNowItems).toHaveLength(1)
      expect(store.playNowItems[0].id).toBe(99)
      // A soft transition was initiated
      expect(cables.updateForSlideType).toHaveBeenCalled()
      expect(engine.isTransitioning.value).toBe(true)
    })
  })

  describe('clearPlayNowAfter', () => {
    it('should exit playNow after next transition completes', () => {
      const { store, engine } = setupEngine()

      const playNowItem = createPlaylistItem({ id: 99 })
      store.enterPlayNow(playNowItem)

      engine.setClearPlayNowAfter()
      expect(engine.clearPlayNowAfter.value).toBe(true)

      // Simulate a soft transition followed by completion
      engine.prepareTransition(false)
      vi.advanceTimersByTime(250)
      engine.onTransitionComplete()

      expect(store.playNow).toBe(false)
      expect(engine.clearPlayNowAfter.value).toBe(false)
    })
  })
})
