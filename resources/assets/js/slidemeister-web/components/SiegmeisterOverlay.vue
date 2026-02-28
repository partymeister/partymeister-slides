<template>
  <div
    ref="containerRef"
    class="siegmeister-overlay"
    :class="{ active: isAnimating }"
  />
</template>

<script setup lang="ts">
import { ref } from 'vue'

defineProps<{
  isAnimating: boolean
}>()

const containerRef = ref<HTMLElement | null>(null)

defineExpose({ containerRef })
</script>

<style scoped>
.siegmeister-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  z-index: 1002;
  pointer-events: none;
}
</style>

<style>
.slidemeister-bars {
  position: absolute;
  opacity: 0.5;
}

.slidemeister-bars.active {
  z-index: 10000;
}

.slidemeister-bars.blink {
  animation: blinker 1s linear infinite;
}

@keyframes blinker {
  50% {
    opacity: 0.1;
  }
}
</style>
