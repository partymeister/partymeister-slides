<script setup lang="ts">
defineProps<{
  html: string
  label: string
  isVideo?: boolean
  videoPreview?: string
}>()
</script>

<template>
  <div class="slide-preview">
    <div class="slide-wrapper">
      <div v-if="isVideo && videoPreview" class="slide-container">
        <img :src="videoPreview" class="video-preview" />
      </div>
      <div v-else class="slide-container" v-html="html" />
    </div>
    <div class="slide-label">{{ label }}</div>
  </div>
</template>

<style scoped>
.slide-preview {
  display: inline-block;
  margin: 0 15px 15px 0;
}

.slide-wrapper {
  width: 480px;
  height: 270px;
  overflow: hidden;
  background: #000;
  border: 1px solid #333;
}

.slide-container {
  width: 960px;
  height: 540px;
  position: relative;
  transform: scale(0.5);
  transform-origin: top left;
}

.video-preview {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.slide-label {
  text-align: center;
  padding: 4px;
  font-size: 12px;
  color: #666;
}
</style>
