<template>
  <audio ref="audioRef" preload="auto" />
</template>

<script setup lang="ts">
import { ref } from 'vue'

const audioRef = ref<HTMLAudioElement | null>(null)

defineExpose({ audioRef })
</script>
