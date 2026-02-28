<template>
  <div class="slide-renderer">
    <template v-if="item">
      <!-- Video slide -->
      <video
        v-if="item.type === 'video' && item.file_association"
        ref="videoRef"
        :src="item.file_association.file.url"
        class="slide-video"
        muted
        playsinline
        @error="hasError = true"
      />
      <!-- HTML slide (priority over image — HTML slides also have file_association as fallback) -->
      <div
        v-else-if="item.slide?.cached_html_final"
        class="slide-html"
        v-html="item.slide.cached_html_final"
      />
      <!-- Image slide with file -->
      <img
        v-else-if="item.file_association"
        :src="item.file_association.file.url"
        class="slide-image"
        @error="hasError = true"
      />
      <!-- Fallback -->
      <div v-else class="slide-unavailable">
        Slide unavailable
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import type { PlaylistItem } from '@/types/playlist'

defineProps<{ item?: PlaylistItem }>()

const videoRef = ref<HTMLVideoElement | null>(null)
const hasError = ref(false)

function playVideo() {
  videoRef.value?.play().catch(() => {})
}

function pauseVideo() {
  videoRef.value?.pause()
}

defineExpose({ playVideo, pauseVideo })
</script>

<style scoped>
.slide-renderer {
  position: absolute;
  top: 0;
  left: 0;
  width: 960px;
  height: 540px;
  overflow: hidden;
  isolation: isolate; /* contain inner z-indices from injected HTML */
}

.slide-image,
.slide-video {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.slide-html {
  width: 100%;
  height: 100%;
  position: relative;
}

.slide-unavailable {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #666;
  font-size: 24px;
  background: #111;
}
</style>

<!-- Unscoped styles for v-html slide content (injected HTML from server) -->
<style>
.slide-html .moveable {
  display: flex;
  font-family: "Roboto", sans-serif;
  z-index: 1000;
  position: absolute;
  width: 300px;
  height: 200px;
  text-align: center;
  font-size: 40px;
  margin: 0 auto;
  font-weight: 100;
  background-size: cover;
  background-repeat: no-repeat;
  background-position: center center;
}

.slide-html .snappable-shadow {
  width: 200px;
  height: 200px;
  position: absolute;
  visibility: hidden;
}

.slide-html .medium-editor-element {
  z-index: 10000;
  width: 98%;
  margin: 0 auto;
  text-align: left;
  font-family: Arial, sans-serif;
}

.slide-html .medium-editor-element p {
  margin-bottom: 0;
}

.slide-html .slidemeister-overlay {
  display: none;
}

/* Hide preview-only layers (static BG, panels) — CABLES.gl provides the animated background */
.slide-html div[data-partymeister-slides-visibility='preview'] {
  display: none;
}
</style>
