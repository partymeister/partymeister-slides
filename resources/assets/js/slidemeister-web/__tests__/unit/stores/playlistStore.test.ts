// __tests__/unit/stores/playlistStore.test.ts
import { setActivePinia, createPinia } from 'pinia'
import { usePlaylistStore } from '@/stores/playlistStore'
import { createPlaylist, resetPlaylistIdCounter } from '../../fixtures/playlists'
import { createPlaylistItem, resetItemIdCounter } from '../../fixtures/items'

describe('playlistStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    resetPlaylistIdCounter()
    resetItemIdCounter()
  })

  describe('initial state', () => {
    it('should start with empty state', () => {
      const store = usePlaylistStore()
      expect(store.cachedPlaylists).toEqual([])
      expect(store.currentPlaylist).toBeNull()
      expect(store.items).toEqual([])
      expect(store.currentItemIndex).toBeNull()
      expect(store.playNow).toBe(false)
    })

    it('should have null playNow-related state initially', () => {
      const store = usePlaylistStore()
      expect(store.playNowItems).toEqual([])
      expect(store.currentPlayNowIndex).toBeNull()
      expect(store.savedPlaylistId).toBeNull()
      expect(store.savedItemIndex).toBeNull()
    })

    it('should have default zoom of 2', () => {
      const store = usePlaylistStore()
      expect(store.zoom).toBe(2)
    })
  })

  describe('cachePlaylist', () => {
    it('should add a new playlist to cache', () => {
      const store = usePlaylistStore()
      const playlist = createPlaylist()
      store.cachePlaylist(playlist)
      expect(store.cachedPlaylists).toHaveLength(1)
      expect(store.cachedPlaylists[0].id).toBe(playlist.id)
    })

    it('should add multiple playlists to cache', () => {
      const store = usePlaylistStore()
      const playlist1 = createPlaylist()
      const playlist2 = createPlaylist()
      store.cachePlaylist(playlist1)
      store.cachePlaylist(playlist2)
      expect(store.cachedPlaylists).toHaveLength(2)
    })

    it('should always update an existing playlist with fresh data', () => {
      const store = usePlaylistStore()
      const playlist = createPlaylist({ name: 'Original' })
      store.cachePlaylist(playlist)
      const updated = { ...playlist, name: 'Updated' }
      store.cachePlaylist(updated)
      expect(store.cachedPlaylists).toHaveLength(1)
      expect(store.cachedPlaylists[0].name).toBe('Updated')
    })

    it('should preserve callback metadata from existing cache when not in new data', () => {
      const store = usePlaylistStore()
      const playlist = createPlaylist({ callbacks: true, callback_url: 'http://example.com/cb' })
      store.cachePlaylist(playlist)
      const updated = { ...playlist, name: 'Updated', callbacks: undefined, callback_url: undefined }
      store.cachePlaylist(updated as any)
      expect(store.cachedPlaylists[0].callbacks).toBe(true)
      expect(store.cachedPlaylists[0].callback_url).toBe('http://example.com/cb')
    })

    it('should hot-swap items when the active playlist is updated', () => {
      const store = usePlaylistStore()
      const playlist = createPlaylist()
      store.cachePlaylist(playlist)
      store.setActivePlaylist(playlist.id)
      const newItems = [createPlaylistItem(), createPlaylistItem()]
      const updated = { ...playlist, items: newItems }
      store.cachePlaylist(updated)
      expect(store.items).toHaveLength(2)
    })

    it('should clamp currentItemIndex when updated playlist is shorter', () => {
      const store = usePlaylistStore()
      const playlist = createPlaylist({ items: [createPlaylistItem(), createPlaylistItem(), createPlaylistItem()] })
      store.cachePlaylist(playlist)
      store.setActivePlaylist(playlist.id)
      store.currentItemIndex = 2
      const updated = { ...playlist, items: [createPlaylistItem()] }
      store.cachePlaylist(updated)
      expect(store.currentItemIndex).toBe(0)
    })

    it('should not clamp currentItemIndex when updated playlist has enough items', () => {
      const store = usePlaylistStore()
      const playlist = createPlaylist({ items: [createPlaylistItem(), createPlaylistItem(), createPlaylistItem()] })
      store.cachePlaylist(playlist)
      store.setActivePlaylist(playlist.id)
      store.currentItemIndex = 1
      const newItems = [createPlaylistItem(), createPlaylistItem(), createPlaylistItem()]
      const updated = { ...playlist, items: newItems }
      store.cachePlaylist(updated)
      expect(store.currentItemIndex).toBe(1)
    })
  })

  describe('setActivePlaylist', () => {
    it('should set the current playlist from cache', () => {
      const store = usePlaylistStore()
      const playlist = createPlaylist()
      store.cachePlaylist(playlist)
      store.setActivePlaylist(playlist.id)
      expect(store.currentPlaylist?.id).toBe(playlist.id)
      expect(store.items).toEqual(playlist.items)
    })

    it('should return false if playlist not in cache', () => {
      const store = usePlaylistStore()
      const result = store.setActivePlaylist(999)
      expect(result).toBe(false)
    })

    it('should return true when playlist is found', () => {
      const store = usePlaylistStore()
      const playlist = createPlaylist()
      store.cachePlaylist(playlist)
      const result = store.setActivePlaylist(playlist.id)
      expect(result).toBe(true)
    })

    it('should not affect currentItemIndex when switching playlists', () => {
      const store = usePlaylistStore()
      const playlist1 = createPlaylist()
      const playlist2 = createPlaylist()
      store.cachePlaylist(playlist1)
      store.cachePlaylist(playlist2)
      store.setActivePlaylist(playlist1.id)
      store.currentItemIndex = 2
      store.setActivePlaylist(playlist2.id)
      expect(store.currentItemIndex).toBe(2)
    })
  })

  describe('navigation indices', () => {
    it('should compute nextIndex wrapping around', () => {
      const store = usePlaylistStore()
      const playlist = createPlaylist({ items: [createPlaylistItem(), createPlaylistItem(), createPlaylistItem()] })
      store.cachePlaylist(playlist)
      store.setActivePlaylist(playlist.id)
      store.currentItemIndex = 2
      expect(store.nextIndex).toBe(0)
    })

    it('should compute nextIndex for middle items', () => {
      const store = usePlaylistStore()
      const playlist = createPlaylist({ items: [createPlaylistItem(), createPlaylistItem(), createPlaylistItem()] })
      store.cachePlaylist(playlist)
      store.setActivePlaylist(playlist.id)
      store.currentItemIndex = 0
      expect(store.nextIndex).toBe(1)
    })

    it('should compute previousIndex wrapping around', () => {
      const store = usePlaylistStore()
      const playlist = createPlaylist({ items: [createPlaylistItem(), createPlaylistItem(), createPlaylistItem()] })
      store.cachePlaylist(playlist)
      store.setActivePlaylist(playlist.id)
      store.currentItemIndex = 0
      expect(store.previousIndex).toBe(2)
    })

    it('should compute previousIndex for middle items', () => {
      const store = usePlaylistStore()
      const playlist = createPlaylist({ items: [createPlaylistItem(), createPlaylistItem(), createPlaylistItem()] })
      store.cachePlaylist(playlist)
      store.setActivePlaylist(playlist.id)
      store.currentItemIndex = 2
      expect(store.previousIndex).toBe(1)
    })

    it('should return null indices when no playlist', () => {
      const store = usePlaylistStore()
      expect(store.nextIndex).toBeNull()
      expect(store.previousIndex).toBeNull()
    })

    it('should return null indices when currentItemIndex is null', () => {
      const store = usePlaylistStore()
      const playlist = createPlaylist()
      store.cachePlaylist(playlist)
      store.setActivePlaylist(playlist.id)
      expect(store.nextIndex).toBeNull()
      expect(store.previousIndex).toBeNull()
    })
  })

  describe('current/next/previous item getters', () => {
    it('should return the correct items based on indices', () => {
      const store = usePlaylistStore()
      const items = [createPlaylistItem(), createPlaylistItem(), createPlaylistItem()]
      const playlist = createPlaylist({ items })
      store.cachePlaylist(playlist)
      store.setActivePlaylist(playlist.id)
      store.currentItemIndex = 1
      expect(store.currentItem?.id).toBe(items[1].id)
      expect(store.nextItem?.id).toBe(items[2].id)
      expect(store.previousItem?.id).toBe(items[0].id)
    })

    it('should return undefined when no currentItemIndex', () => {
      const store = usePlaylistStore()
      expect(store.currentItem).toBeUndefined()
      expect(store.nextItem).toBeUndefined()
      expect(store.previousItem).toBeUndefined()
    })

    it('should wrap around for next/previous items', () => {
      const store = usePlaylistStore()
      const items = [createPlaylistItem(), createPlaylistItem(), createPlaylistItem()]
      const playlist = createPlaylist({ items })
      store.cachePlaylist(playlist)
      store.setActivePlaylist(playlist.id)
      store.currentItemIndex = 2
      expect(store.nextItem?.id).toBe(items[0].id)
      expect(store.previousItem?.id).toBe(items[1].id)
    })
  })

  describe('playNow', () => {
    it('should save current state when entering playNow', () => {
      const store = usePlaylistStore()
      const playlist = createPlaylist()
      store.cachePlaylist(playlist)
      store.setActivePlaylist(playlist.id)
      store.currentItemIndex = 1

      const playNowItem = createPlaylistItem({ id: 99 })
      store.enterPlayNow(playNowItem)

      expect(store.playNow).toBe(true)
      expect(store.savedPlaylistId).toBe(playlist.id)
      expect(store.savedItemIndex).toBe(1)
      expect(store.playNowItems).toHaveLength(1)
      expect(store.playNowItems[0].id).toBe(99)
    })

    it('should stack multiple playNow items', () => {
      const store = usePlaylistStore()
      const playlist = createPlaylist()
      store.cachePlaylist(playlist)
      store.setActivePlaylist(playlist.id)

      store.enterPlayNow(createPlaylistItem({ id: 99 }))
      store.enterPlayNow(createPlaylistItem({ id: 100 }))

      expect(store.playNowItems).toHaveLength(2)
      expect(store.currentPlayNowIndex).toBe(1)
    })

    it('should not overwrite saved state when stacking playNow items', () => {
      const store = usePlaylistStore()
      const playlist = createPlaylist()
      store.cachePlaylist(playlist)
      store.setActivePlaylist(playlist.id)
      store.currentItemIndex = 1

      store.enterPlayNow(createPlaylistItem({ id: 99 }))
      store.enterPlayNow(createPlaylistItem({ id: 100 }))

      expect(store.savedItemIndex).toBe(1)
      expect(store.savedPlaylistId).toBe(playlist.id)
    })

    it('should exit playNow and restore saved state', () => {
      const store = usePlaylistStore()
      const playlist = createPlaylist()
      store.cachePlaylist(playlist)
      store.setActivePlaylist(playlist.id)
      store.currentItemIndex = 1

      store.enterPlayNow(createPlaylistItem({ id: 99 }))
      store.exitPlayNow()

      expect(store.playNow).toBe(false)
      expect(store.playNowItems).toEqual([])
      expect(store.currentItemIndex).toBe(1)
    })

    it('should clear saved state on exitPlayNow', () => {
      const store = usePlaylistStore()
      const playlist = createPlaylist()
      store.cachePlaylist(playlist)
      store.setActivePlaylist(playlist.id)
      store.currentItemIndex = 1

      store.enterPlayNow(createPlaylistItem({ id: 99 }))
      store.exitPlayNow()

      expect(store.savedPlaylistId).toBeNull()
      expect(store.savedItemIndex).toBeNull()
      expect(store.currentPlayNowIndex).toBeNull()
    })

    it('should return playNow item as effectiveCurrentItem when in playNow mode', () => {
      const store = usePlaylistStore()
      const playlist = createPlaylist()
      store.cachePlaylist(playlist)
      store.setActivePlaylist(playlist.id)

      const playNowItem = createPlaylistItem({ id: 99 })
      store.enterPlayNow(playNowItem)

      expect(store.effectiveCurrentItem?.id).toBe(99)
    })

    it('should return normal currentItem as effectiveCurrentItem when not in playNow mode', () => {
      const store = usePlaylistStore()
      const items = [createPlaylistItem(), createPlaylistItem()]
      const playlist = createPlaylist({ items })
      store.cachePlaylist(playlist)
      store.setActivePlaylist(playlist.id)
      store.currentItemIndex = 0

      expect(store.effectiveCurrentItem?.id).toBe(items[0].id)
    })

    it('should return latest stacked playNow item as effectiveCurrentItem', () => {
      const store = usePlaylistStore()
      const playlist = createPlaylist()
      store.cachePlaylist(playlist)
      store.setActivePlaylist(playlist.id)

      store.enterPlayNow(createPlaylistItem({ id: 99 }))
      store.enterPlayNow(createPlaylistItem({ id: 100 }))

      expect(store.effectiveCurrentItem?.id).toBe(100)
    })
  })

  describe('zoom', () => {
    it('should calculate zoom based on window size', () => {
      const store = usePlaylistStore()
      store.updateZoom(1920, 1080)
      expect(store.zoom).toBe(2)
    })

    it('should use the smaller scale factor', () => {
      const store = usePlaylistStore()
      store.updateZoom(1920, 540)
      expect(store.zoom).toBe(1)
    })

    it('should handle exact base dimensions', () => {
      const store = usePlaylistStore()
      store.updateZoom(960, 540)
      expect(store.zoom).toBe(1)
    })

    it('should handle smaller than base dimensions', () => {
      const store = usePlaylistStore()
      store.updateZoom(480, 270)
      expect(store.zoom).toBe(0.5)
    })
  })
})
