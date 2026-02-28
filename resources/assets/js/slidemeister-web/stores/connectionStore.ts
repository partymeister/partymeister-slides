// stores/connectionStore.ts
import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useConnectionStore = defineStore('connection', () => {
  const isConnected = ref(false)
  const error = ref<string | null>(null)
  const channelName = ref<string | null>(null)
  const lastEventAt = ref<number | null>(null)

  function setConnected(channel: string) {
    isConnected.value = true
    error.value = null
    channelName.value = channel
  }

  function setDisconnected(errorMessage?: string) {
    isConnected.value = false
    error.value = errorMessage ?? null
  }

  function recordEvent() {
    lastEventAt.value = Date.now()
  }

  return {
    isConnected,
    error,
    channelName,
    lastEventAt,
    setConnected,
    setDisconnected,
    recordEvent,
  }
})
