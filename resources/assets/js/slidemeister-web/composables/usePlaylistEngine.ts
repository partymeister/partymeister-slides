import { ref, type Ref } from 'vue'
import type { usePlaylistStore } from '@/stores/playlistStore'
import type { useStorage } from '@/composables/useStorage'
import type { useMidi } from '@/composables/useMidi'
import type { useCables } from '@/composables/useCables'
import type { PlaylistItem } from '@/types/playlist'

interface SlideLayerRef {
  transition(item: PlaylistItem, hard: boolean): void
  setDisplayed(item: PlaylistItem): void
}

export function usePlaylistEngine(
  playlistStore: ReturnType<typeof usePlaylistStore>,
  storage: ReturnType<typeof useStorage>,
  midi: ReturnType<typeof useMidi>,
  cables: ReturnType<typeof useCables>,
  slideLayerRef: Ref<SlideLayerRef | null>,
  statusCallback?: () => void,
  beforeSeekCallback?: () => void,
  logEvent?: (type: 'socket' | 'transition' | 'midi' | 'error', message: string) => void,
) {
  const clearPlayNowAfter = ref(false)

  let slideTimeout: ReturnType<typeof setTimeout> | null = null
  let callbackTimeout: ReturnType<typeof setTimeout> | null = null

  // Reactive timer state for debug panel countdowns
  const slideTimerEnd = ref<number | null>(null)
  const slideTimerDuration = ref<number | null>(null)
  const callbackTimerEnd = ref<number | null>(null)
  const callbackTimerDuration = ref<number | null>(null)

  // ── Timeouts ──────────────────────────────────────────────────────

  function clearTimeouts(): void {
    if (slideTimeout !== null) {
      clearTimeout(slideTimeout)
      slideTimeout = null
    }
    slideTimerEnd.value = null
    slideTimerDuration.value = null
    if (callbackTimeout !== null) {
      clearTimeout(callbackTimeout)
      callbackTimeout = null
    }
    callbackTimerEnd.value = null
    callbackTimerDuration.value = null
  }

  function setSlideTimeout(): void {
    // Clear any existing slide timeout to prevent double-fire
    if (slideTimeout !== null) {
      clearTimeout(slideTimeout)
      slideTimeout = null
    }
    slideTimerEnd.value = null
    slideTimerDuration.value = null

    if (playlistStore.playNow) return
    if (playlistStore.currentItemIndex === null) return
    if (playlistStore.items.length === 0) return

    const item = playlistStore.items[playlistStore.currentItemIndex]
    if (!item || item.is_advanced_manually) return

    const durationMs = item.duration * 1000
    slideTimerEnd.value = Date.now() + durationMs
    slideTimerDuration.value = durationMs

    slideTimeout = setTimeout(() => {
      slideTimerEnd.value = null
      slideTimerDuration.value = null
      seekToNext(false)
    }, durationMs)
  }

  function setCallbackDelay(): void {
    // Clear any existing callback timeout to prevent double-fire
    if (callbackTimeout !== null) {
      clearTimeout(callbackTimeout)
      callbackTimeout = null
    }
    callbackTimerEnd.value = null
    callbackTimerDuration.value = null

    if (playlistStore.playNow) return
    if (playlistStore.currentItemIndex === null) return

    const playlist = playlistStore.currentPlaylist
    if (!playlist || !playlist.callbacks) return

    const item = playlistStore.items[playlistStore.currentItemIndex]
    if (!item || !item.callback_hash) return

    const durationMs = item.callback_delay * 1000
    callbackTimerEnd.value = Date.now() + durationMs
    callbackTimerDuration.value = durationMs

    callbackTimeout = setTimeout(async () => {
      callbackTimerEnd.value = null
      callbackTimerDuration.value = null
      try {
        const response = await fetch(playlist.callback_url + item.callback_hash, {
          credentials: 'same-origin',
        })
        if (response.ok) {
          const data = await response.json().catch(() => null)
          if (data?.status === 'already_fired') {
            logEvent?.('socket', `Callback ${item.callback_hash.slice(0, 8)}… already fired`)
          } else {
            logEvent?.('socket', `Callback ${item.callback_hash.slice(0, 8)}… fired OK`)
          }
        } else {
          logEvent?.('error', `Callback ${item.callback_hash.slice(0, 8)}… HTTP ${response.status}`)
        }
      } catch (err: unknown) {
        const msg = err instanceof Error ? err.message : String(err)
        logEvent?.('error', `Callback ${item.callback_hash.slice(0, 8)}… failed: ${msg}`)
      }
    }, durationMs)
  }

  // ── After Seek ────────────────────────────────────────────────────

  async function afterSeek(): Promise<void> {
    await storage.save('currentItem', playlistStore.currentItemIndex)

    // Track position per playlist
    if (playlistStore.currentPlaylist && playlistStore.currentItemIndex !== null) {
      playlistStore.savePosition(playlistStore.currentPlaylist.id, playlistStore.currentItemIndex)
    }

    if (playlistStore.currentItemIndex !== null) {
      const item = playlistStore.items[playlistStore.currentItemIndex]
      if (item && item.midi_note > 0) {
        midi.playNote(item.midi_note)
      }
    }

    setSlideTimeout()
    setCallbackDelay()
    if (statusCallback) statusCallback()
  }

  // ── CABLES Background ──────────────────────────────────────────────

  function updateCablesForItem(item: PlaylistItem, animate: boolean): void {
    const slideType = item.slide_type ?? ''
    const html = item.slide?.cached_html_final ?? null
    let entryType: string | undefined

    if (slideType === 'compo' && item.metadata) {
      try {
        const meta = typeof item.metadata === 'string'
          ? JSON.parse(item.metadata)
          : item.metadata
        entryType = meta?.remote_type?.toLowerCase() || 'party'
      } catch {
        entryType = 'party'
      }
    }

    cables.updateForSlideType(slideType, animate, html, entryType)
  }

  // ── Transition Complete ────────────────────────────────────────────

  function onTransitionComplete(): void {
    if (clearPlayNowAfter.value) {
      playlistStore.exitPlayNow()
      clearPlayNowAfter.value = false
    }
    afterSeek()
  }

  // ── Navigation ────────────────────────────────────────────────────

  function seekToNext(hard = true): void {
    clearTimeouts()
    if (beforeSeekCallback) beforeSeekCallback()

    let baseIndex = playlistStore.currentItemIndex ?? 0
    if (playlistStore.playNow && playlistStore.savedItemIndex !== null) {
      baseIndex = playlistStore.savedItemIndex
    }

    const length = playlistStore.items.length
    if (length === 0) return

    const targetIndex = (baseIndex + 1) % length
    const targetItem = playlistStore.items[targetIndex]

    // Commit index immediately — SlideLayer holds the outgoing slide internally
    playlistStore.currentItemIndex = targetIndex

    updateCablesForItem(targetItem, !hard)
    slideLayerRef.value?.transition(targetItem, hard)

    // For hard transitions, afterSeek runs immediately (no animation)
    if (hard) afterSeek()
  }

  function seekToPrevious(hard = true): void {
    clearTimeouts()
    if (beforeSeekCallback) beforeSeekCallback()

    let baseIndex = playlistStore.currentItemIndex ?? 0
    if (playlistStore.playNow && playlistStore.savedItemIndex !== null) {
      baseIndex = playlistStore.savedItemIndex
    }

    const length = playlistStore.items.length
    if (length === 0) return

    const targetIndex = (baseIndex - 1 + length) % length
    const targetItem = playlistStore.items[targetIndex]

    playlistStore.currentItemIndex = targetIndex

    updateCablesForItem(targetItem, !hard)
    slideLayerRef.value?.transition(targetItem, hard)

    if (hard) afterSeek()
  }

  function seekToIndex(index: number, hard = false): void {
    if (index < 0 || index >= playlistStore.items.length) return

    clearTimeouts()
    if (beforeSeekCallback) beforeSeekCallback()

    const targetItem = playlistStore.items[index]
    playlistStore.currentItemIndex = index

    updateCablesForItem(targetItem, !hard)
    slideLayerRef.value?.transition(targetItem, hard)
    if (hard) afterSeek()
  }

  // ── PlayNow ───────────────────────────────────────────────────────

  function seekToPlayNow(item: PlaylistItem): void {
    clearTimeouts()
    playlistStore.enterPlayNow(item)

    updateCablesForItem(item, true)
    slideLayerRef.value?.transition(item, false)
  }

  function setClearPlayNowAfter(): void {
    clearPlayNowAfter.value = true
  }

  return {
    // State
    clearPlayNowAfter,

    // Navigation
    seekToNext,
    seekToPrevious,
    seekToIndex,

    // Transition
    onTransitionComplete,

    // Timers
    setSlideTimeout,
    setCallbackDelay,
    clearTimeouts,
    slideTimerEnd,
    slideTimerDuration,
    callbackTimerEnd,
    callbackTimerDuration,

    // After seek
    afterSeek,

    // PlayNow
    seekToPlayNow,
    setClearPlayNowAfter,

    // Status
    updateCablesForItem,
  }
}
