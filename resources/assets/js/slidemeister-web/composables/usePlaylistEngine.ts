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
) {
  const clearPlayNowAfter = ref(false)

  let slideTimeout: ReturnType<typeof setTimeout> | null = null
  let callbackTimeout: ReturnType<typeof setTimeout> | null = null

  // ── Timeouts ──────────────────────────────────────────────────────

  function clearTimeouts(): void {
    if (slideTimeout !== null) {
      clearTimeout(slideTimeout)
      slideTimeout = null
    }
    if (callbackTimeout !== null) {
      clearTimeout(callbackTimeout)
      callbackTimeout = null
    }
  }

  function setSlideTimeout(): void {
    if (playlistStore.playNow) return
    if (playlistStore.currentItemIndex === null) return
    if (playlistStore.items.length === 0) return

    const item = playlistStore.items[playlistStore.currentItemIndex]
    if (!item || item.is_advanced_manually) return

    slideTimeout = setTimeout(() => {
      seekToNext(false)
    }, item.duration * 1000)
  }

  function setCallbackDelay(): void {
    if (playlistStore.playNow) return
    if (playlistStore.currentItemIndex === null) return

    const playlist = playlistStore.currentPlaylist
    if (!playlist || !playlist.callbacks) return

    const item = playlistStore.items[playlistStore.currentItemIndex]
    if (!item || !item.callback_hash) return

    callbackTimeout = setTimeout(async () => {
      try {
        await fetch(playlist.callback_url + item.callback_hash)
      } catch {
        // ignore callback errors
      }
    }, item.callback_delay * 1000)
  }

  // ── After Seek ────────────────────────────────────────────────────

  async function afterSeek(): Promise<void> {
    await storage.save('currentItem', playlistStore.currentItemIndex)

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

  function seekToIndex(index: number): void {
    if (index < 0 || index >= playlistStore.items.length) return

    clearTimeouts()

    const targetItem = playlistStore.items[index]
    playlistStore.currentItemIndex = index

    updateCablesForItem(targetItem, false)
    slideLayerRef.value?.transition(targetItem, true)
    afterSeek()
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

    // After seek
    afterSeek,

    // PlayNow
    seekToPlayNow,
    setClearPlayNowAfter,

    // Status
    updateCablesForItem,
  }
}
