<script setup lang="ts">
import { computed, ref } from 'vue'

const props = defineProps<{
  html: string
  label: string
  isVideo?: boolean
  videoPreview?: string
  zoom?: number
}>()

const scale = computed(() => props.zoom ?? 0.5)
const wrapperWidth = computed(() => `${960 * scale.value}px`)
const wrapperHeight = computed(() => `${540 * scale.value}px`)
const previewFailed = ref(false)
</script>

<template>
  <div class="slide-preview">
    <div class="slide-wrapper" :style="{ width: wrapperWidth, height: wrapperHeight }">
      <div v-if="isVideo" class="slide-container" :style="{ transform: `scale(${scale})` }">
        <img v-if="videoPreview && !previewFailed" :src="videoPreview" class="video-preview" @error="previewFailed = true" />
        <div v-else class="video-placeholder">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#555" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="5 3 19 12 5 21 5 3" fill="#333" stroke="#555" />
          </svg>
          <span>Video</span>
        </div>
      </div>
      <div v-else class="slide-container" :style="{ transform: `scale(${scale})` }" v-html="html" />
    </div>
    <div class="slide-label">{{ label }}</div>
  </div>
</template>

<style scoped>
.slide-preview {
  display: inline-block;
}

.slide-wrapper {
  overflow: hidden;
  background: #000;
  border: 1px solid #333;
  border-radius: 2px;
}

.slide-container {
  width: 960px;
  height: 540px;
  position: relative;
  transform-origin: top left;
}

.video-preview {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.video-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  background: #111;
  color: #555;
  font-size: 16px;
  font-weight: 600;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.slide-label {
  text-align: center;
  padding: 4px 0;
  font-size: 11px;
  color: #666;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
