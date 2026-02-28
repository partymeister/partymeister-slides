// stores/playlistStore.ts
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { Playlist, PlaylistItem } from '@/types/playlist'

const BASE_WIDTH = 960
const BASE_HEIGHT = 540

export const usePlaylistStore = defineStore('playlist', () => {
  // Cache
  const cachedPlaylists = ref<Playlist[]>([])

  // Active playlist
  const currentPlaylist = ref<Playlist | null>(null)
  const items = ref<PlaylistItem[]>([])
  const currentItemIndex = ref<number | null>(null)

  // PlayNow
  const playNow = ref(false)
  const playNowItems = ref<PlaylistItem[]>([])
  const currentPlayNowIndex = ref<number | null>(null)
  const savedPlaylistId = ref<number | null>(null)
  const savedItemIndex = ref<number | null>(null)

  // Per-playlist position tracking
  const playlistPositions = ref<Record<number, number>>({})

  // Audio
  const videoMuted = ref(false)

  // Display
  const zoom = ref(2)
  const windowWidth = ref(window.innerWidth)
  const windowHeight = ref(window.innerHeight)
  const currentBackground = ref<string | null>(null)

  // Navigation indices
  const nextIndex = computed(() => {
    if (currentItemIndex.value === null || items.value.length === 0) return null
    return (currentItemIndex.value + 1) % items.value.length
  })

  const previousIndex = computed(() => {
    if (currentItemIndex.value === null || items.value.length === 0) return null
    return (currentItemIndex.value - 1 + items.value.length) % items.value.length
  })

  // Item getters
  const currentItem = computed(() => {
    if (currentItemIndex.value === null) return undefined
    return items.value[currentItemIndex.value]
  })

  const nextItem = computed(() => {
    if (nextIndex.value === null) return undefined
    return items.value[nextIndex.value]
  })

  const previousItem = computed(() => {
    if (previousIndex.value === null) return undefined
    return items.value[previousIndex.value]
  })

  const effectiveCurrentItem = computed(() => {
    if (playNow.value && currentPlayNowIndex.value !== null) {
      return playNowItems.value[currentPlayNowIndex.value]
    }
    return currentItem.value
  })

  // Actions
  function cachePlaylist(playlist: Playlist) {
    const existingIndex = cachedPlaylists.value.findIndex((p) => p.id === playlist.id)

    if (existingIndex >= 0) {
      const existing = cachedPlaylists.value[existingIndex]

      // Always update callback metadata (comes from WebSocket event, not API)
      if (playlist.callbacks !== undefined) {
        existing.callbacks = playlist.callbacks
        if (currentPlaylist.value?.id === playlist.id) currentPlaylist.value.callbacks = playlist.callbacks
      }
      if (playlist.callback_url !== undefined) {
        existing.callback_url = playlist.callback_url
        if (currentPlaylist.value?.id === playlist.id) currentPlaylist.value.callback_url = playlist.callback_url
      }

      if (existing.updated_at.date !== playlist.updated_at.date) {
        cachedPlaylists.value[existingIndex] = playlist

        // Hot-swap if this is the active playlist
        if (currentPlaylist.value?.id === playlist.id) {
          currentPlaylist.value = playlist
          items.value = playlist.items

          if (currentItemIndex.value !== null && currentItemIndex.value >= items.value.length) {
            currentItemIndex.value = Math.max(0, items.value.length - 1)
          }
        }
      }
    } else {
      cachedPlaylists.value.push(playlist)
    }
  }

  function removeFromCache(playlistId: number): void {
    cachedPlaylists.value = cachedPlaylists.value.filter(p => p.id !== playlistId)
    delete playlistPositions.value[playlistId]
    if (currentPlaylist.value?.id === playlistId) {
      currentPlaylist.value = null
      items.value = []
      currentItemIndex.value = null
    }
  }

  function setActivePlaylist(playlistId: number): boolean {
    const playlist = cachedPlaylists.value.find((p) => p.id === playlistId)
    if (!playlist) return false

    // Save current position before switching
    if (currentPlaylist.value && currentItemIndex.value !== null) {
      playlistPositions.value[currentPlaylist.value.id] = currentItemIndex.value
    }

    currentPlaylist.value = playlist
    items.value = playlist.items
    return true
  }

  function getSavedPosition(playlistId: number): number | undefined {
    return playlistPositions.value[playlistId]
  }

  function savePosition(playlistId: number, index: number): void {
    playlistPositions.value[playlistId] = index
  }

  function enterPlayNow(item: PlaylistItem) {
    if (!playNow.value) {
      // Save current state
      savedPlaylistId.value = currentPlaylist.value?.id ?? null
      savedItemIndex.value = currentItemIndex.value
      playNowItems.value = [item]
      currentPlayNowIndex.value = 0
    } else {
      // Stack additional playNow item
      playNowItems.value.push(item)
      currentPlayNowIndex.value = playNowItems.value.length - 1
    }
    playNow.value = true
  }

  function exitPlayNow() {
    playNow.value = false
    playNowItems.value = []
    currentPlayNowIndex.value = null

    // Restore saved position
    if (savedItemIndex.value !== null) {
      currentItemIndex.value = savedItemIndex.value
    }
    savedPlaylistId.value = null
    savedItemIndex.value = null
  }

  function updateZoom(w: number, h: number) {
    const scaleX = w / BASE_WIDTH
    const scaleY = h / BASE_HEIGHT
    zoom.value = Math.min(scaleX, scaleY)
    windowWidth.value = w
    windowHeight.value = h
  }

  return {
    // State
    cachedPlaylists,
    currentPlaylist,
    items,
    currentItemIndex,
    playNow,
    playNowItems,
    currentPlayNowIndex,
    savedPlaylistId,
    savedItemIndex,
    zoom,
    windowWidth,
    windowHeight,
    currentBackground,
    videoMuted,
    // Getters
    nextIndex,
    previousIndex,
    currentItem,
    nextItem,
    previousItem,
    effectiveCurrentItem,
    // Actions
    cachePlaylist,
    removeFromCache,
    setActivePlaylist,
    getSavedPosition,
    savePosition,
    playlistPositions,
    enterPlayNow,
    exitPlayNow,
    updateZoom,
  }
})
