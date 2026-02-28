<template>
  <!-- Tier 0: Nothing -->

  <!-- Tier 1: Minimal status bar -->
  <div v-if="tier === 1" class="debug-bar">
    <span class="status-dot" :class="connectionStore.isConnected ? 'connected' : 'disconnected'" />
    <span v-if="configStore.clientName" class="client-name">{{ configStore.clientName }}</span>
    <span class="separator">|</span>
    <span v-if="playlistStore.currentPlaylist">
      {{ playlistStore.currentPlaylist.name }} — {{ (playlistStore.currentItemIndex ?? 0) + 1 }}/{{ playlistStore.items.length }}
    </span>
    <span v-else>No playlist</span>
    <span class="separator">|</span>
    <span>MIDI: {{ midiOutputName || 'None' }}</span>
    <span v-if="playlistStore.videoMuted" class="mute-badge">MUTED</span>
    <span v-if="playlistStore.playNow" class="playnow-badge">PLAY NOW</span>
  </div>

  <!-- Tier 2: Full debug panel -->
  <div v-else-if="tier === 2" class="debug-panel">
    <div class="debug-section">
      <h4>Connection</h4>
      <div v-if="configStore.clientName">Client: <span class="text-green">{{ configStore.clientName }}</span></div>
      <div>WebSocket: <span :class="connectionStore.isConnected ? 'text-green' : 'text-red'">
        {{ connectionStore.isConnected ? 'Connected' : 'Disconnected' }}
      </span></div>
      <div>Channel: {{ connectionStore.channelName || 'None' }}</div>
      <div>MIDI: {{ midiOutputName || 'No device' }}</div>
      <div>Video audio: <span :class="playlistStore.videoMuted ? 'text-red' : 'text-green'">
        {{ playlistStore.videoMuted ? 'Muted' : 'On' }}
      </span></div>
      <div v-if="connectionStore.lastEventAt">
        Last event: {{ timeSinceLastEvent }}
      </div>
    </div>

    <div class="debug-section">
      <h4>Playlist</h4>
      <template v-if="playlistStore.currentPlaylist">
        <div>Name: {{ playlistStore.currentPlaylist.name }}</div>
        <div>Position: {{ (playlistStore.currentItemIndex ?? 0) + 1 }} / {{ playlistStore.items.length }}</div>
        <div>Slide type: {{ playlistStore.currentItem?.slide_type || '(none)' }}</div>
        <div>Auto-advance: {{ playlistStore.currentItem?.is_advanced_manually ? 'Manual' : `${playlistStore.currentItem?.duration}s` }}</div>
      </template>
      <div v-else>No playlist loaded</div>
      <div v-if="playlistStore.playNow" class="playnow-indicator">
        PlayNow active ({{ playlistStore.playNowItems.length }} items)
      </div>
    </div>

    <div class="debug-section">
      <h4>Cache</h4>
      <div v-for="p in playlistStore.cachedPlaylists" :key="p.id" class="cache-entry">
        <span :class="{ 'text-green': playlistStore.currentPlaylist?.id === p.id }">
          {{ p.name }} ({{ p.items.length }} items)
        </span>
        <button class="debug-btn-sm" @click="$emit('removePlaylist', p.id)" title="Remove from cache">x</button>
      </div>
      <div v-if="playlistStore.cachedPlaylists.length === 0">Empty</div>
      <button class="debug-btn" @click="$emit('clearCache')">Empty cache</button>
    </div>

    <div class="debug-section">
      <h4>Timers</h4>
      <div v-if="slideTimerEnd">
        Auto-advance: <span class="text-green">{{ slideCountdown }}s</span>
        / {{ ((slideTimerDuration ?? 0) / 1000).toFixed(1) }}s
      </div>
      <div v-else>Auto-advance: <span class="text-dim">Manual</span></div>
      <div v-if="callbackTimerEnd">
        Callback: <span class="text-yellow">{{ callbackCountdown }}s</span>
        / {{ ((callbackTimerDuration ?? 0) / 1000).toFixed(1) }}s
      </div>
      <div v-else>Callback: <span class="text-dim">None</span></div>
    </div>

    <div class="debug-section">
      <h4>Event Log</h4>
      <div class="event-log">
        <div v-for="event in recentEvents" :key="event.id" :class="['log-entry', `log-${event.type}`]">
          <span class="log-time">{{ event.time }}</span>
          <span>{{ event.message }}</span>
        </div>
        <div v-if="recentEvents.length === 0" class="log-empty">No events yet</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { usePlaylistStore } from '@/stores/playlistStore'
import { useConnectionStore } from '@/stores/connectionStore'
import { useConfigStore } from '@/stores/configStore'

const props = defineProps<{
  tier: 0 | 1 | 2
  midiOutputName: string | null
  slideTimerEnd: number | null
  slideTimerDuration: number | null
  callbackTimerEnd: number | null
  callbackTimerDuration: number | null
}>()

defineEmits<{
  clearCache: []
  removePlaylist: [id: number]
}>()

const playlistStore = usePlaylistStore()
const connectionStore = useConnectionStore()
const configStore = useConfigStore()

// Event log
export interface LogEvent {
  id: number
  type: 'socket' | 'transition' | 'midi' | 'error'
  message: string
  time: string
}

const recentEvents = ref<LogEvent[]>([])
let eventId = 0

function addLogEvent(type: LogEvent['type'], message: string) {
  const now = new Date()
  const time = now.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
  recentEvents.value.unshift({ id: ++eventId, type, message, time })
  if (recentEvents.value.length > 50) {
    recentEvents.value.pop()
  }
}

// Time ticker (200ms for smooth countdowns)
const now = ref(Date.now())
let timer: ReturnType<typeof setInterval> | null = null

onMounted(() => {
  timer = setInterval(() => { now.value = Date.now() }, 200)
})

// Countdown computeds
const slideCountdown = computed(() => {
  if (!props.slideTimerEnd) return '0.0'
  return Math.max(0, (props.slideTimerEnd - now.value) / 1000).toFixed(1)
})

const callbackCountdown = computed(() => {
  if (!props.callbackTimerEnd) return '0.0'
  return Math.max(0, (props.callbackTimerEnd - now.value) / 1000).toFixed(1)
})

onUnmounted(() => {
  if (timer) clearInterval(timer)
})

const timeSinceLastEvent = computed(() => {
  if (!connectionStore.lastEventAt) return 'Never'
  const seconds = Math.floor((now.value - connectionStore.lastEventAt) / 1000)
  if (seconds < 60) return `${seconds}s ago`
  return `${Math.floor(seconds / 60)}m ${seconds % 60}s ago`
})

defineExpose({ addLogEvent })
</script>

<style scoped>
.debug-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 9998;
  background: rgba(0, 0, 0, 0.7);
  color: #ccc;
  font-family: monospace;
  font-size: 12px;
  padding: 4px 12px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.debug-panel {
  position: fixed;
  top: 0;
  right: 0;
  z-index: 9998;
  background: rgba(0, 0, 0, 0.85);
  color: #ccc;
  font-family: monospace;
  font-size: 12px;
  padding: 12px;
  max-width: 480px;
  max-height: 100vh;
  overflow-y: auto;
}

.debug-section {
  margin-bottom: 12px;
  padding-bottom: 8px;
  border-bottom: 1px solid #333;
}

h4 {
  margin: 0 0 4px 0;
  color: #fff;
  font-size: 13px;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
}

.connected { background: #22c55e; }
.disconnected { background: #ef4444; }
.text-green { color: #22c55e; }
.text-red { color: #ef4444; }
.text-yellow { color: #f59e0b; }
.text-dim { color: #555; }
.separator { color: #555; }
.client-name { color: #60a5fa; font-weight: bold; }

.mute-badge {
  background: #ef4444;
  color: #fff;
  padding: 1px 6px;
  border-radius: 3px;
  font-weight: bold;
  font-size: 11px;
}

.playnow-badge, .playnow-indicator {
  background: #f59e0b;
  color: #000;
  padding: 1px 6px;
  border-radius: 3px;
  font-weight: bold;
  font-size: 11px;
}

.event-log {
  max-height: 200px;
  overflow-y: auto;
}

.log-entry {
  padding: 2px 0;
  border-bottom: 1px solid #222;
}

.log-time {
  color: #666;
  margin-right: 8px;
}

.log-socket { color: #60a5fa; }
.log-transition { color: #34d399; }
.log-midi { color: #a78bfa; }
.log-error { color: #f87171; }

.debug-btn {
  margin-top: 4px;
  background: #333;
  color: #ccc;
  border: 1px solid #555;
  padding: 4px 8px;
  cursor: pointer;
  font-family: monospace;
  font-size: 11px;
}

.debug-btn:hover {
  background: #444;
}

.cache-entry {
  padding: 2px 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.debug-btn-sm {
  background: #433;
  color: #f87171;
  border: 1px solid #555;
  padding: 0 5px;
  cursor: pointer;
  font-family: monospace;
  font-size: 10px;
  line-height: 16px;
  flex-shrink: 0;
}

.debug-btn-sm:hover {
  background: #644;
}

.log-empty {
  color: #555;
  font-style: italic;
}
</style>
