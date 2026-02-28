<template>
  <div
    ref="containerRef"
    class="siegmeister-overlay"
    :class="{ active: isAnimating }"
  />
</template>

<script setup lang="ts">
import { ref } from 'vue'

const props = defineProps<{
  isAnimating: boolean
}>()

const containerRef = ref<HTMLElement | null>(null)

defineExpose({ containerRef })
</script>

<style scoped>
.siegmeister-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 1002;
  pointer-events: none;
}
</style>
