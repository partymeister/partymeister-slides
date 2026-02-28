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
  toggleMute: () => void
}

export function useKeyboard(deps: KeyboardDeps) {
  const debugTier = ref(0) // 0=off, 1=minimal, 2=full
  const showKeyboardHelp = ref(false)

  function handleKeydown(event: KeyboardEvent): void {
    console.log(`[Keyboard] key=${event.key} shift=${event.shiftKey}`)

    switch (event.key) {
      case 'ArrowRight':
        event.preventDefault()
        console.log('[Keyboard] seekToNext', event.shiftKey ? '(hard)' : '(soft)')
        if (deps.isPlayNow() && deps.hasPlaylistItems()) {
          deps.setClearPlayNowAfter()
        }
        deps.seekToNext(event.shiftKey)
        break

      case 'ArrowLeft':
        event.preventDefault()
        console.log('[Keyboard] seekToPrevious', event.shiftKey ? '(hard)' : '(soft)')
        if (deps.isPlayNow() && deps.hasPlaylistItems()) {
          deps.setClearPlayNowAfter()
        }
        deps.seekToPrevious(event.shiftKey)
        break

      case ' ': // Space
        event.preventDefault()
        console.log('[Keyboard] Space — slideType:', deps.getCurrentSlideType())
        if (deps.getCurrentSlideType() === 'siegmeister_bars') {
          console.log('[Keyboard] Triggering siegmeister')
          deps.triggerSiegmeister()
        }
        break

      case 'Escape':
        event.preventDefault()
        console.log('[Keyboard] Escape — stopping jingle + MIDI stop signal')
        deps.stopJingle()
        deps.sendStopSignal()
        break

      case 'd':
        // Toggle tier 1 (bottom bar)
        debugTier.value = debugTier.value === 1 ? 0 : 1
        console.log('[Keyboard] Debug tier:', debugTier.value)
        break

      case 'D':
        // Toggle tier 2 (full panel)
        debugTier.value = debugTier.value === 2 ? 0 : 2
        console.log('[Keyboard] Debug tier:', debugTier.value)
        break

      case 'm':
        console.log('[Keyboard] Toggle video mute')
        deps.toggleMute()
        break

      case '?':
        showKeyboardHelp.value = !showKeyboardHelp.value
        break

      case 'F1': case 'F2': case 'F3': case 'F4': {
        event.preventDefault()
        const jingleIndex = parseInt(event.key.substring(1))
        console.log(`[Keyboard] Playing jingle ${jingleIndex}`)
        deps.playJingle(jingleIndex)
        break
      }

      case 'F5': case 'F6': case 'F7': case 'F8': case 'F9': case 'F10': {
        event.preventDefault()
        const midiIndex = parseInt(event.key.substring(1))
        console.log(`[Keyboard] MIDI-only note for index ${midiIndex}`)
        deps.playMidiOnly(midiIndex)
        break
      }
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
