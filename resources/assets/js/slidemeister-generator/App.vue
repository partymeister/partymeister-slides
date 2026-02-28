<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApi } from '@common/composables/useApi'
import { generateCompetitionPlaylist, resizeTextAndSerialize } from '@/composables/useSlideReplacer'
import SlidePreview from '@/components/SlidePreview.vue'
import type { CompetitionData, GeneratedSlide } from '@/types/generator'

const api = useApi()
const state = ref<'loading' | 'preview' | 'saving' | 'saved' | 'error'>('loading')
const errorMessage = ref('')
const competitionName = ref('')
const slides = ref<GeneratedSlide[]>([])
const competitionData = ref<CompetitionData | null>(null)

const competitionId = window.COMPETITION_ID

onMounted(async () => {
  try {
    const data = await api.request<CompetitionData>(
      'GET',
      `/api/competitions/${competitionId}/playlist-data`
    )
    competitionData.value = data
    competitionName.value = data.competition.name
    const generated = generateCompetitionPlaylist(data)
    resizeTextAndSerialize(generated)
    slides.value = generated
    state.value = 'preview'
  } catch (err) {
    errorMessage.value = err instanceof Error ? err.message : 'Failed to load data'
    state.value = 'error'
  }
})

async function savePlaylist() {
  if (!competitionData.value) return

  state.value = 'saving'
  try {
    const payload = {
      slides: slides.value
        .filter(s => !s.type.startsWith('video_'))
        .map(s => ({
          key: s.key,
          type: s.type,
          name: s.name,
          definitions: JSON.stringify({ elements: s.elements }),
          cached_html_preview: s.html,
          cached_html_final: s.html,
          ...(s.id !== undefined ? { id: s.id } : {}),
        })),
      videos: competitionData.value.videos.map((v, i) => ({
        key: `video_${i + 1}`,
        file_id: v.file_id,
        data: v.data,
      })),
    }

    await api.request('POST', `/api/competitions/${competitionId}/playlist`, payload)
    state.value = 'saved'
  } catch (err) {
    errorMessage.value = err instanceof Error ? err.message : 'Failed to save'
    state.value = 'error'
  }
}

function getVideoPreview(slide: GeneratedSlide): string {
  if (!competitionData.value) return ''
  const index = parseInt(slide.type.replace('video_', '')) - 1
  return competitionData.value.videos[index]?.preview ?? ''
}
</script>

<template>
  <div id="generator">
    <div v-if="state === 'loading'" class="status">
      Loading competition data...
    </div>

    <div v-else-if="state === 'error'" class="status error">
      {{ errorMessage }}
    </div>

    <div v-else-if="state === 'preview' || state === 'saving'">
      <div class="toolbar">
        <h2>Competition: {{ competitionName }}</h2>
        <div class="actions">
          <button
            class="btn btn-success"
            :disabled="state === 'saving'"
            @click="savePlaylist"
          >
            {{ state === 'saving' ? 'Saving...' : 'Save Playlist' }}
          </button>
        </div>
      </div>

      <div class="slides-grid">
        <SlidePreview
          v-for="slide in slides"
          :key="slide.key"
          :html="slide.html"
          :label="slide.name"
          :is-video="slide.type.startsWith('video_')"
          :video-preview="getVideoPreview(slide)"
        />
      </div>
    </div>

    <div v-else-if="state === 'saved'" class="status success">
      Playlist saved successfully!
    </div>
  </div>
</template>

<style>
body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  background: #1a1a2e;
  color: #eee;
  padding: 20px;
}

.toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding: 10px 0;
  border-bottom: 1px solid #333;
}

.toolbar h2 {
  margin: 0;
  font-size: 18px;
}

.btn {
  padding: 8px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.btn-success {
  background: #28a745;
  color: white;
}

.btn-success:hover {
  background: #218838;
}

.btn-success:disabled {
  background: #666;
  cursor: not-allowed;
}

.slides-grid {
  display: flex;
  flex-wrap: wrap;
}

.status {
  text-align: center;
  padding: 40px;
  font-size: 18px;
}

.error {
  color: #ff6b6b;
}

.success {
  color: #51cf66;
}
</style>
