import { ref } from 'vue'
import type { Ref } from 'vue'
import type { JingleConfig } from '@/types/config'

export function useJingles(
  audioRef: Ref<HTMLAudioElement | null>,
  getMidiNote: (index: number) => number,
  midiPlayNote: (note: number) => void,
) {
  const jingleUrls = ref<Record<number, string | null>>({})

  function loadJingles(config: JingleConfig) {
    for (let i = 1; i <= 4; i++) {
      jingleUrls.value[i] = config[`jingle_${i}`] ?? null
    }
  }

  function play(index: number): void {
    const url = jingleUrls.value[index]
    if (!url || !audioRef.value) return
    audioRef.value.src = url
    audioRef.value.currentTime = 0
    audioRef.value.play()

    const midiNote = getMidiNote(index)
    if (midiNote > 0) {
      midiPlayNote(midiNote)
    }
  }

  function stop(): void {
    if (!audioRef.value) return
    audioRef.value.pause()
    audioRef.value.currentTime = 0
  }

  return { loadJingles, play, stop, jingleUrls }
}
