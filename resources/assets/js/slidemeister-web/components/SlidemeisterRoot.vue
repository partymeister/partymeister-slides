<template>
  <div class="slidemeister-root">
    <CablesCanvas @patch-ready="onCablesReady" />

    <SlideLayer
      ref="slideLayerRef"
      :zoom="playlistStore.zoom"
      :window-width="playlistStore.windowWidth"
      :window-height="playlistStore.windowHeight"
      @transition-complete="engine.onTransitionComplete"
    />

    <SiegmeisterOverlay
      ref="siegmeisterOverlayRef"
      :is-animating="siegmeisterAnimating"
    />

    <ConnectionStatus />

    <DebugPanel
      ref="debugPanelRef"
      :tier="keyboard.debugTier.value"
      :midi-output-name="midi.outputName.value"
      :slide-timer-end="engine.slideTimerEnd.value"
      :slide-timer-duration="engine.slideTimerDuration.value"
      :callback-timer-end="engine.callbackTimerEnd.value"
      :callback-timer-duration="engine.callbackTimerDuration.value"
      @clear-cache="clearCache"
      @remove-playlist="removePlaylist"
    />

    <KeyboardHelp
      v-if="keyboard.showKeyboardHelp.value"
      @close="keyboard.showKeyboardHelp.value = false"
    />

    <JinglePlayer ref="jinglePlayerRef" />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import axios from 'axios'

// Components
import CablesCanvas from './CablesCanvas.vue'
import SlideLayer from './SlideLayer.vue'
import SiegmeisterOverlay from './SiegmeisterOverlay.vue'
import ConnectionStatus from './ConnectionStatus.vue'
import DebugPanel from './DebugPanel.vue'
import KeyboardHelp from './KeyboardHelp.vue'
import JinglePlayer from './JinglePlayer.vue'

// Stores
import { usePlaylistStore } from '@/stores/playlistStore'
import { useConfigStore } from '@/stores/configStore'
import { useConnectionStore } from '@/stores/connectionStore'

// Composables
import { useStorage } from '@/composables/useStorage'
import { useMidi } from '@/composables/useMidi'
import { useCables } from '@/composables/useCables'
import { usePlaylistEngine } from '@/composables/usePlaylistEngine'
import { useEcho } from '@/composables/useEcho'
import { useJingles } from '@/composables/useJingles'
import { useKeyboard } from '@/composables/useKeyboard'
import { useSiegmeister } from '@/composables/useSiegmeister'
import { useWindowResize } from '@/composables/useWindowResize'

// Types
import type { SlideClientApiResponse } from '@/types/config'
import type { SiegmeisterBarData } from '@/types/playlist'

// Stores
const playlistStore = usePlaylistStore()
const configStore = useConfigStore()
const connectionStore = useConnectionStore()

// Component refs
const slideLayerRef = ref<InstanceType<typeof SlideLayer> | null>(null)
const jinglePlayerRef = ref<InstanceType<typeof JinglePlayer> | null>(null)
const siegmeisterOverlayRef = ref<InstanceType<typeof SiegmeisterOverlay> | null>(null)
const debugPanelRef = ref<InstanceType<typeof DebugPanel> | null>(null)

// Composables
const storage = useStorage()
const midi = useMidi()
const cables = useCables()

// Siegmeister (declared before engine so clearSiegmeisterBars can reference it)
const siegmeisterAnimating = ref(false)
let siegmeisterBarElements: HTMLElement[] = []
let siegmeisterMetadata: SiegmeisterBarData[] = []

let skipBarClear = false

const siegmeister = useSiegmeister(
  () => configStore.prizegivingBarColor,
  () => configStore.prizegivingBarBlinkColor,
  () => {
    siegmeisterAnimating.value = false
    skipBarClear = true
    engine.seekToNext(true)
    skipBarClear = false
  },
)

function clearSiegmeisterBars() {
  if (skipBarClear) return
  const container = siegmeisterOverlayRef.value?.containerRef
  if (container) siegmeister.clearBars(container)
  siegmeisterAnimating.value = false
}

const engine = usePlaylistEngine(
  playlistStore,
  storage,
  midi,
  cables,
  slideLayerRef,
  updateStatus,
  clearSiegmeisterBars,
  log,
)

function triggerSiegmeister() {
  const item = playlistStore.effectiveCurrentItem
  console.log('[Siegmeister] item:', item?.slide_type, 'metadata:', item?.metadata)
  if (!item?.metadata) {
    console.warn('[Siegmeister] No metadata on current item')
    return
  }

  let metadata = item.metadata
  if (typeof metadata === 'string') {
    try { metadata = JSON.parse(metadata) } catch (e) {
      console.warn('[Siegmeister] Failed to parse metadata:', e)
      return
    }
  }

  // Handle both array format and legacy object format (keyed by element name)
  if (!Array.isArray(metadata)) {
    if (typeof metadata === 'object' && metadata !== null) {
      console.log('[Siegmeister] Converting object metadata to array')
      metadata = Object.values(metadata)
    } else {
      console.warn('[Siegmeister] Metadata is not array or object:', typeof metadata)
      return
    }
  }

  console.log('[Siegmeister] Bar count:', metadata.length, 'bars:', metadata)

  const container = siegmeisterOverlayRef.value?.containerRef
  if (!container) {
    console.warn('[Siegmeister] No container ref')
    return
  }

  // Clear any existing bars before rendering new ones
  siegmeister.clearBars(container)
  siegmeisterAnimating.value = true
  siegmeisterMetadata = metadata as SiegmeisterBarData[]
  siegmeisterBarElements = siegmeister.renderBars(siegmeisterMetadata, playlistStore.zoom, container, playlistStore.windowWidth, playlistStore.windowHeight)
  siegmeister.animateBars(siegmeisterMetadata, siegmeisterBarElements, playlistStore.zoom)
}

// Jingles (audioRef will be available after mount)
const audioRef = computed(() => jinglePlayerRef.value?.audioRef ?? null)
const jingles = useJingles(
  audioRef,
  (index: number) => configStore.getMidiNote(index),
  (note: number) => midi.playNote(note),
)

// Debug event log helper
function log(type: 'socket' | 'transition' | 'midi' | 'error', message: string) {
  debugPanelRef.value?.addLogEvent(type, message)
}

// Echo (WebSocket)
const echo = useEcho(
  playlistStore,
  connectionStore,
  engine,
  triggerSiegmeister,
  storage,
  undefined, // echoFactory
  log,
)

// Keyboard
const keyboard = useKeyboard({
  seekToNext: (hard) => {
    log('transition', `Next (${hard ? 'hard' : 'soft'})`)
    engine.seekToNext(hard)
  },
  seekToPrevious: (hard) => {
    log('transition', `Previous (${hard ? 'hard' : 'soft'})`)
    engine.seekToPrevious(hard)
  },
  playJingle: (index) => {
    log('midi', `Jingle ${index}`)
    jingles.play(index)
  },
  playMidiOnly: (index) => {
    const note = configStore.getMidiNote(index)
    log('midi', `MIDI note ${note} (F${index})`)
    if (note > 0) midi.playNote(note)
  },
  sendStopSignal: () => {
    log('midi', 'Stop signal (Escape)')
    midi.sendStopSignal()
  },
  stopJingle: () => jingles.stop(),
  triggerSiegmeister: () => {
    log('transition', 'Siegmeister triggered')
    triggerSiegmeister()
  },
  getCurrentSlideType: () => playlistStore.effectiveCurrentItem?.slide_type ?? '',
  hasPlaylistItems: () => playlistStore.items.length > 0,
  isPlayNow: () => playlistStore.playNow,
  setClearPlayNowAfter: () => engine.setClearPlayNowAfter(),
  toggleMute: () => {
    playlistStore.videoMuted = !playlistStore.videoMuted
    log('midi', `Video ${playlistStore.videoMuted ? 'muted' : 'unmuted'}`)
  },
})

// Window resize
useWindowResize((w, h) => playlistStore.updateZoom(w, h))

// CABLES ready flag
const cablesReady = ref(false)

function onCablesReady() {
  cablesReady.value = true
  cables.isReady.value = true
}

// Status update
async function updateStatus(): Promise<void> {
  try {
    const config = configStore.slideClientConfig
    if (!config?.server) return

    await axios.post(
      `${config.server}/ajax/slidemeister-web/${config.client}/status`,
      {
        cached_playlists: playlistStore.cachedPlaylists.map(p => ({
          id: p.id,
          updated_at: p.updated_at,
        })),
        current_playlist_id: playlistStore.currentPlaylist?.id,
        current_item_id: playlistStore.currentItem?.id,
      },
      {
        headers: {
          Authorization: `Bearer ${(globalThis as any).TOKEN}`,
        },
      },
    )
  } catch {
    // Status update failures are non-critical
  }
}

// Remove single playlist from cache
async function removePlaylist(id: number) {
  playlistStore.removeFromCache(id)
  await storage.save('cachedPlaylists', playlistStore.cachedPlaylists)
  updateStatus()
}

// Cache clearing
async function clearCache() {
  await storage.clear()
  playlistStore.cachedPlaylists = []
  playlistStore.currentPlaylist = null
  playlistStore.items = []
  playlistStore.currentItemIndex = null
  cables.resetBackground()
  updateStatus()
}

// Initialization
onMounted(async () => {
  // 1. Load config from server
  await loadConfiguration()

  // 2. Initialize MIDI
  midi.enable()

  // 3. Restore state from storage
  await restoreState()

  // 4. Connect WebSocket
  if (configStore.serverConfig) {
    echo.connect(configStore.serverConfig)
  }

  // 5. Load jingles
  if (configStore.jingles) {
    jingles.loadJingles(configStore.jingles)
  }
})

async function loadConfiguration(): Promise<void> {
  try {
    const baseUrl = (globalThis as any).BASE_URL
    const token = (globalThis as any).TOKEN
    const clientId = window.location.pathname.substring(
      window.location.pathname.lastIndexOf('/') + 1,
    )

    const response = await axios.get<SlideClientApiResponse>(
      `${baseUrl}/api/slide_clients/${clientId}`,
      { headers: { Authorization: `Bearer ${token}` } },
    )

    const data = response.data.data
    configStore.clientName = data.name ?? null
    configStore.setSlideClientConfig(data.configuration)
    const serverConfig = { ...data.websocket, client: String(data.id) }
    configStore.setServerConfig(serverConfig)
    configStore.setJingles(data.jingles)

    // Persist to storage for offline recovery
    await storage.save('slideClientConfiguration', data.configuration)
    await storage.save('serverConfiguration', serverConfig)
  } catch (e) {
    // Try to load from storage
    const savedConfig = await storage.load<any>('slideClientConfiguration')
    const savedServer = await storage.load<any>('serverConfiguration')
    if (savedConfig) configStore.setSlideClientConfig(savedConfig)
    if (savedServer) configStore.setServerConfig(savedServer)

    connectionStore.setDisconnected('Failed to load configuration from server')
  }
}

async function restoreState(): Promise<void> {
  // Restore cached playlists
  const cached = await storage.load<any[]>('cachedPlaylists')
  if (cached) {
    cached.forEach(p => playlistStore.cachePlaylist(p))
  }

  // Restore current playlist
  const savedPlaylist = await storage.load<any>('playlist')
  if (savedPlaylist) {
    playlistStore.cachePlaylist(savedPlaylist)
    playlistStore.setActivePlaylist(savedPlaylist.id)
  }

  // Restore current item index
  const savedIndex = await storage.load<number>('currentItem')

  // Wait for CABLES to be ready before first seek
  if (!cablesReady.value) {
    await new Promise<void>(resolve => {
      const unwatch = watch(cablesReady, (ready) => {
        if (ready) {
          unwatch()
          resolve()
        }
      })
      // Safety timeout - don't wait forever
      setTimeout(() => {
        unwatch()
        resolve()
      }, 3000)
    })
  }

  // Seek to saved position (hard = instant, no animation on restore)
  engine.seekToIndex(savedIndex ?? 0, true)
}
</script>

<style>
.slidemeister-root {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  overflow: hidden;
  background: #000;
}
</style>
