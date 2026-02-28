import { ref, onMounted, onUnmounted } from 'vue'

interface KeyboardDeps {
  seekToNext: (hard: boolean) => void
  seekToPrevious: (hard: boolean) => void
  playJingle: (index: number) => void
  playMidiOnly: (index: number) => void
  sendStopSignal: () => void
  stopJingle: () => void
  triggerSiegmeister: () => void
  getCurrentSlideType: () => string
  hasPlaylistItems: () => boolean
  isPlayNow: () => boolean
  setClearPlayNowAfter: () => void
}

export function useKeyboard(deps: KeyboardDeps) {
  const debugTier = ref(0) // 0=off, 1=minimal, 2=full
  const showKeyboardHelp = ref(false)

  function handleKeydown(event: KeyboardEvent): void {
    switch (event.key) {
      case 'ArrowRight':
        event.preventDefault()
        if (deps.isPlayNow() && deps.hasPlaylistItems()) {
          deps.setClearPlayNowAfter()
        }
        deps.seekToNext(event.shiftKey)
        break

      case 'ArrowLeft':
        event.preventDefault()
        if (deps.isPlayNow() && deps.hasPlaylistItems()) {
          deps.setClearPlayNowAfter()
        }
        deps.seekToPrevious(event.shiftKey)
        break

      case ' ': // Space
        event.preventDefault()
        if (deps.getCurrentSlideType() === 'siegmeister_bars') {
          deps.triggerSiegmeister()
        }
        break

      case 'Escape':
        event.preventDefault()
        deps.stopJingle()
        deps.sendStopSignal()
        break

      case 'd':
        debugTier.value = (debugTier.value + 1) % 3
        break

      case '?':
        showKeyboardHelp.value = !showKeyboardHelp.value
        break

      case 'F1': case 'F2': case 'F3': case 'F4':
        event.preventDefault()
        deps.playJingle(parseInt(event.key.substring(1)))
        break

      case 'F5': case 'F6': case 'F7': case 'F8': case 'F9': case 'F10':
        event.preventDefault()
        deps.playMidiOnly(parseInt(event.key.substring(1)))
        break
    }
  }

  function enable(): void {
    window.addEventListener('keydown', handleKeydown)
  }

  function disable(): void {
    window.removeEventListener('keydown', handleKeydown)
  }

  onMounted(enable)
  onUnmounted(disable)

  return { debugTier, showKeyboardHelp, enable, disable }
}
