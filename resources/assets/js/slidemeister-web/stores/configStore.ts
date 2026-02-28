// stores/configStore.ts
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { SlideClientConfiguration, ServerConfiguration, JingleConfig } from '@/types/config'

export const useConfigStore = defineStore('config', () => {
  const slideClientConfig = ref<SlideClientConfiguration | null>(null)
  const serverConfig = ref<ServerConfiguration | null>(null)
  const jingles = ref<JingleConfig | null>(null)
  const clientName = ref<string | null>(null)

  const prizegivingBarColor = computed(() => {
    return slideClientConfig.value?.prizegiving_bar_color ?? '#00ff00'
  })

  const prizegivingBarBlinkColor = computed(() => {
    return slideClientConfig.value?.prizegiving_bar_blink_color ?? '#ff0000'
  })

  function setSlideClientConfig(config: SlideClientConfiguration) {
    slideClientConfig.value = config
  }

  function setServerConfig(config: ServerConfiguration) {
    serverConfig.value = config
  }

  function setJingles(config: JingleConfig) {
    jingles.value = config
  }

  function getMidiNote(index: number): number {
    if (!slideClientConfig.value) return 0
    const key = `midi_note_jingle_${index}`
    return (slideClientConfig.value[key] as number) ?? 0
  }

  return {
    slideClientConfig,
    serverConfig,
    jingles,
    clientName,
    prizegivingBarColor,
    prizegivingBarBlinkColor,
    setSlideClientConfig,
    setServerConfig,
    setJingles,
    getMidiNote,
  }
})
