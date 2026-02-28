import { ref, computed } from 'vue'

// WebMidi is imported dynamically to allow mocking
let WebMidiModule: any = null

export function useMidi() {
  const isAvailable = ref(false)
  const outputs = ref<any[]>([])

  async function enable(): Promise<boolean> {
    try {
      if (!WebMidiModule) {
        const mod = await import('webmidi')
        WebMidiModule = mod.WebMidi
      }
      await WebMidiModule.enable({ sysex: true })
      outputs.value = [...WebMidiModule.outputs]
      isAvailable.value = outputs.value.length > 0
      return isAvailable.value
    } catch (e) {
      console.warn('MIDI not available:', e)
      isAvailable.value = false
      return false
    }
  }

  function playNote(note: number, duration = 1000, channel = 1): void {
    if (note === 0 || !isAvailable.value || outputs.value.length === 0) return
    const output = outputs.value[0]
    output.sendNoteOn(note, { channels: channel, rawAttack: 127 })
    setTimeout(() => {
      output.sendNoteOff(note, { channels: channel })
    }, duration)
  }

  function sendStopSignal(): void {
    playNote(103, 1000, 1)
  }

  const outputName = computed(() => {
    return outputs.value.length > 0 ? outputs.value[0].name : null
  })

  return {
    enable,
    playNote,
    sendStopSignal,
    isAvailable,
    outputName,
    outputs,
  }
}
