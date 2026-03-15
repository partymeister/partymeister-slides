<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApi } from '@common/composables/useApi'
import {
  generateCompetitionPlaylist,
  generateTimetablePlaylist,
  generatePrizegivingPlaylist,
  generateEventPlaylist,
  resizeTextAndSerialize,
} from '@/composables/useSlideReplacer'
import SlidePreview from '@/components/SlidePreview.vue'
import type { CompetitionData, TimetableData, PrizegivingData, EventData, GeneratedSlide } from '@/types/generator'

const api = useApi()
const state = ref<'loading' | 'preview' | 'saving' | 'saved' | 'error' | 'warnings'>('loading')
const errorMessage = ref('')
const warnings = ref<string[]>([])
const title = ref('')
const slides = ref<GeneratedSlide[]>([])
const zoom = ref(0.5)

const generatorType = window.GENERATOR_TYPE || 'competition'
const competitionId = window.COMPETITION_ID
const scheduleId = window.SCHEDULE_ID
const eventId = window.EVENT_ID
const headless = window.HEADLESS || false

// Start page state
const competitions = ref<{ id: number; name: string }[]>([])
const schedules = ref<{ id: number; name: string }[]>([])
const events = ref<{ id: number; name: string }[]>([])
const selectedCompetitionId = ref<number | null>(null)
const selectedScheduleId = ref<number | null>(null)
const selectedEventId = ref<number | null>(null)

// Store raw data for save payloads
let competitionData: CompetitionData | null = null

onMounted(async () => {
  if (generatorType === 'start') {
    await loadStartPage()
    return
  }

  try {
    switch (generatorType) {
      case 'competition':
        await loadCompetition()
        break
      case 'timetable':
        await loadTimetable()
        break
      case 'prizegiving':
        await loadPrizegiving()
        break
      case 'event':
        await loadEvent()
        break
    }
    if (state.value === 'loading') {
      if (headless) {
        await savePlaylist()
      } else {
        state.value = 'preview'
      }
    }
  } catch (err) {
    errorMessage.value = err instanceof Error ? err.message : 'Failed to load data'
    state.value = 'error'
  }
})

async function loadStartPage() {
  try {
    const [comps, scheds, evts] = await Promise.all([
      api.request<{ data: { id: number; name: string }[] }>('GET', '/api/competitions'),
      api.request<{ data: { id: number; name: string }[] }>('GET', '/api/schedules'),
      api.request<{ data: { id: number; name: string }[] }>('GET', '/api/events'),
    ])
    competitions.value = comps.data
    schedules.value = scheds.data
    events.value = evts.data
    if (comps.data.length > 0) selectedCompetitionId.value = comps.data[0].id
    if (scheds.data.length > 0) selectedScheduleId.value = scheds.data[0].id
    if (evts.data.length > 0) selectedEventId.value = evts.data[0].id
    state.value = 'preview'
  } catch (err) {
    errorMessage.value = err instanceof Error ? err.message : 'Failed to load data'
    state.value = 'error'
  }
}

function navigateTo(url: string) {
  window.location.href = url
}

async function loadCompetition() {
  const data = await api.request<CompetitionData & { warnings?: string[] }>(
    'GET',
    `/api/competitions/${competitionId}/playlist-data`
  )

  if (data.warnings && data.warnings.length > 0) {
    warnings.value = data.warnings
    state.value = 'warnings'
    return
  }

  competitionData = data
  title.value = data.competition.name
  const generated = generateCompetitionPlaylist(data)
  resizeTextAndSerialize(generated)
  slides.value = generated
}

async function loadTimetable() {
  const data = await api.request<TimetableData>(
    'GET',
    `/api/schedules/${scheduleId}/playlist-data`
  )
  title.value = data.schedule.name
  const generated = generateTimetablePlaylist(data)
  resizeTextAndSerialize(generated)
  slides.value = generated
}

async function loadEvent() {
  const data = await api.request<EventData>(
    'GET',
    `/api/events/${eventId}/playlist-data`
  )
  title.value = data.event.name
  const generated = generateEventPlaylist(data)
  resizeTextAndSerialize(generated)
  slides.value = generated
}

async function loadPrizegiving() {
  const data = await api.request<PrizegivingData>(
    'GET',
    `/api/prizegiving/playlist-data`
  )
  title.value = 'Prizegiving'
  const generated = generatePrizegivingPlaylist(data)
  resizeTextAndSerialize(generated)
  slides.value = generated
}

function buildSlidePayloads(): Record<string, unknown>[] {
  return slides.value
    .filter(s => !s.type.startsWith('video_'))
    .map(s => {
      const defs = JSON.stringify({ elements: s.elements })
      try {
        JSON.parse(defs)
      } catch (e) {
        throw new Error(`Invalid JSON in definitions for slide "${s.name}": ${(e as Error).message}`)
      }
      return {
        key: s.key,
        type: s.type,
        name: s.name,
        definitions: defs,
        cached_html_preview: s.html,
        cached_html_final: s.html,
        ...(s.id !== undefined ? { id: s.id } : {}),
        ...((s as GeneratedSlide & { meta?: string }).meta !== undefined
          ? { meta: (s as GeneratedSlide & { meta?: string }).meta }
          : {}),
      }
    })
}

async function savePlaylist() {
  state.value = 'saving'
  try {
    const slidePayloads = buildSlidePayloads()

    switch (generatorType) {
      case 'competition': {
        if (!competitionData) return
        const payload = {
          slides: slidePayloads,
          videos: competitionData.videos.map((v, i) => ({
            key: `video_${i + 1}`,
            file_id: v.file_id,
            data: v.data,
          })),
        }
        await api.request('POST', `/api/competitions/${competitionId}/playlist`, payload)
        break
      }
      case 'timetable':
        await api.request('POST', `/api/schedules/${scheduleId}/playlist`, { slides: slidePayloads })
        break
      case 'prizegiving':
        await api.request('POST', `/api/prizegiving/playlist`, { slides: slidePayloads })
        break
      case 'event':
        await api.request('POST', `/api/events/${eventId}/playlist`, { slides: slidePayloads })
        break
    }

    state.value = 'saved'
  } catch (err) {
    errorMessage.value = err instanceof Error ? err.message : 'Failed to save'
    state.value = 'error'
  }
}

function getVideoPreview(slide: GeneratedSlide): string {
  if (!competitionData) return ''
  const index = parseInt(slide.type.replace('video_', '')) - 1
  return competitionData.videos[index]?.preview ?? ''
}

const loadingLabel: Record<string, string> = {
  competition: 'Loading competition data...',
  timetable: 'Loading schedule data...',
  prizegiving: 'Loading prizegiving data...',
  event: 'Loading event data...',
}
</script>

<template>
  <div class="generator-app">
    <div class="generator-toolbar">
      <div v-if="generatorType !== 'start'" class="toolbar-group">
        <button class="toolbar-btn" @click="navigateTo('/slidemeister-generator')">Back</button>
      </div>
      <div class="toolbar-group">
        <span class="toolbar-title">{{ title || 'Generator' }}</span>
      </div>
      <template v-if="generatorType !== 'start' && state !== 'warnings'">
        <div class="toolbar-separator" />
        <div class="toolbar-group zoom-control">
          <span class="zoom-label">Zoom</span>
          <input
            type="range"
            min="0.5"
            max="1"
            step="0.05"
            v-model.number="zoom"
          />
          <span class="zoom-value">{{ Math.round(zoom * 100) }}%</span>
        </div>
        <div class="toolbar-separator" />
        <div class="toolbar-group">
          <span class="slide-count">{{ slides.length }} slides</span>
        </div>
        <div style="flex:1" />
        <div class="toolbar-group">
          <button
            class="toolbar-btn primary"
            :disabled="state === 'saving' || state === 'loading'"
            @click="savePlaylist"
          >
            {{ state === 'saving' ? 'Saving...' : 'Save Playlist' }}
          </button>
        </div>
      </template>
    </div>

    <div class="generator-content">
      <!-- Start / Landing page -->
      <template v-if="generatorType === 'start'">
        <div v-if="state === 'loading'" class="status">Loading...</div>
        <div v-else-if="state === 'error'" class="status error">{{ errorMessage }}</div>
        <div v-else class="start-page">
          <div class="start-card">
            <h2>Competitions</h2>
            <select v-model="selectedCompetitionId" :disabled="competitions.length === 0">
              <option v-if="competitions.length === 0" :value="null">No competitions</option>
              <option v-for="c in competitions" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
            <button
              class="start-card-btn"
              :disabled="!selectedCompetitionId"
              @click="navigateTo(`/slidemeister-generator/competition/${selectedCompetitionId}`)"
            >
              Generate
            </button>
          </div>

          <div class="start-card">
            <h2>Schedules</h2>
            <select v-model="selectedScheduleId" :disabled="schedules.length === 0">
              <option v-if="schedules.length === 0" :value="null">No schedules</option>
              <option v-for="s in schedules" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
            <button
              class="start-card-btn"
              :disabled="!selectedScheduleId"
              @click="navigateTo(`/slidemeister-generator/schedule/${selectedScheduleId}`)"
            >
              Generate
            </button>
          </div>

          <div class="start-card">
            <h2>Events</h2>
            <select v-model="selectedEventId" :disabled="events.length === 0">
              <option v-if="events.length === 0" :value="null">No events</option>
              <option v-for="e in events" :key="e.id" :value="e.id">{{ e.name }}</option>
            </select>
            <button
              class="start-card-btn"
              :disabled="!selectedEventId"
              @click="navigateTo(`/slidemeister-generator/event/${selectedEventId}`)"
            >
              Generate
            </button>
          </div>

          <div class="start-card">
            <h2>Prizegiving</h2>
            <button
              class="start-card-btn"
              @click="navigateTo('/slidemeister-generator/prizegiving')"
            >
              Generate
            </button>
          </div>
        </div>
      </template>

      <!-- Generator content -->
      <template v-else>
        <div v-if="state === 'loading'" class="status">
          {{ loadingLabel[generatorType] }}
        </div>

        <div v-else-if="state === 'error'" class="status error">
          {{ errorMessage }}
        </div>

        <div v-else-if="state === 'warnings'" class="status warnings">
          <div class="warnings-box">
            <h3>Competition is not ready for playlist generation</h3>
            <ul>
              <li v-for="(w, i) in warnings" :key="i">{{ w }}</li>
            </ul>
            <p>Please fix these issues before generating the playlist.</p>
          </div>
        </div>

        <div v-else-if="state === 'saved'" class="status success">
          Playlist saved successfully!
        </div>

        <div v-else class="slides-grid">
          <SlidePreview
            v-for="slide in slides"
            :key="slide.key"
            :html="slide.html"
            :label="slide.name"
            :is-video="slide.type.startsWith('video_')"
            :video-preview="getVideoPreview(slide)"
            :zoom="zoom"
          />
        </div>
      </template>
    </div>

    <div v-if="headless && state === 'saved'" class="generation-complete" style="display:block">done</div>
    <div v-if="headless && state === 'error'" class="generation-error" style="display:block" :data-error="errorMessage">error</div>

    <div class="status-bar">
      <template v-if="generatorType === 'start'">
        <span>Select a generator type</span>
      </template>
      <template v-else>
        <span v-if="state === 'loading'">Loading...</span>
        <span v-else-if="state === 'saving'">Saving playlist...</span>
        <span v-else-if="state === 'saved'" class="saved-text">Saved</span>
        <span v-else-if="state === 'error'" class="error-text">Error</span>
        <span v-else>Ready</span>
        <span class="spacer" />
        <span>{{ slides.length }} slides</span>
      </template>
    </div>
  </div>
</template>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html, body, #app {
  width: 100%;
  height: 100%;
  overflow: hidden;
  background: #1a1a1a;
  color: #eee;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  font-size: 13px;
}

.generator-app {
  display: flex;
  flex-direction: column;
  height: 100vh;
}

.generator-toolbar {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  background: #1e1e1e;
  border-bottom: 1px solid #333;
  flex-shrink: 0;
}

.toolbar-group {
  display: flex;
  align-items: center;
  gap: 4px;
}

.toolbar-separator {
  width: 1px;
  height: 24px;
  background: #444;
}

.toolbar-title {
  font-weight: 600;
  font-size: 13px;
  color: #eee;
}

.toolbar-btn {
  background: #2a2a2a;
  border: 1px solid #444;
  color: #eee;
  padding: 4px 12px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
}

.toolbar-btn:hover:not(:disabled) {
  background: #3a3a3a;
}

.toolbar-btn:disabled {
  opacity: 0.4;
  cursor: default;
}

.toolbar-btn.primary {
  background: #1a5a2a;
  border-color: #2a7a3a;
}

.toolbar-btn.primary:hover:not(:disabled) {
  background: #2a7a3a;
}

.zoom-control {
  gap: 8px;
}

.zoom-label {
  color: #888;
  font-size: 12px;
}

.zoom-control input[type="range"] {
  width: 120px;
  accent-color: #4a9eff;
}

.zoom-value {
  color: #aaa;
  font-size: 12px;
  font-family: monospace;
  min-width: 36px;
}

.slide-count {
  color: #888;
  font-size: 12px;
}

.generator-content {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
}

.slides-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.status {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  font-size: 14px;
  color: #888;
}

.status.error {
  color: #ff6b6b;
}

.status.success {
  color: #2a7a3a;
}

.status.warnings {
  color: #e0a020;
}

.warnings-box {
  background: #2a2200;
  border: 1px solid #554400;
  border-radius: 8px;
  padding: 24px;
  max-width: 500px;
  text-align: left;
}

.warnings-box h3 {
  margin-bottom: 12px;
  font-size: 15px;
  color: #ffcc00;
}

.warnings-box ul {
  list-style: disc;
  padding-left: 20px;
  margin-bottom: 12px;
}

.warnings-box li {
  margin-bottom: 4px;
}

.warnings-box p {
  color: #aa8800;
  font-size: 12px;
}

.status-bar {
  display: flex;
  gap: 16px;
  padding: 4px 12px;
  background: #1a1a1a;
  border-top: 1px solid #333;
  font-size: 12px;
  color: #888;
  align-items: center;
  flex-shrink: 0;
}

.status-bar .spacer {
  flex: 1;
}

.status-bar .saved-text {
  color: #2a7a3a;
  font-weight: 600;
}

.status-bar .error-text {
  color: #ff6b6b;
}

.start-page {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 16px;
  padding: 24px;
  max-width: 1100px;
  margin: 0 auto;
}

.start-card {
  background: #222;
  border: 1px solid #444;
  border-radius: 8px;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.start-card h2 {
  font-size: 15px;
  font-weight: 600;
  color: #eee;
}

.start-card select {
  width: 100%;
  background: #1a1a1a;
  border: 1px solid #444;
  color: #eee;
  padding: 8px 10px;
  border-radius: 4px;
  font-size: 13px;
  min-width: 0;
}

.start-card select:disabled {
  opacity: 0.4;
}

.start-card-btn {
  width: 100%;
  background: #1a5a2a;
  border: 1px solid #2a7a3a;
  color: #eee;
  padding: 8px 12px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
  font-weight: 500;
}

.start-card-btn:hover:not(:disabled) {
  background: #2a7a3a;
}

.start-card-btn:disabled {
  opacity: 0.4;
  cursor: default;
}
</style>
