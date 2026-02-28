<template>
  <div id="cables-container">
    <canvas id="glcanvas" ref="canvasRef" />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

const canvasRef = ref<HTMLCanvasElement | null>(null)
const patchReady = ref(false)

const emit = defineEmits<{
  patchReady: []
}>()

onMounted(() => {
  // Listen for CABLES patch finished loading
  // The patch.js script sets window.patchFinishedLoading when ready
  if (typeof window !== 'undefined') {
    const originalCallback = (window as any).patchFinishedLoading
    ;(window as any).patchFinishedLoading = () => {
      patchReady.value = true
      emit('patchReady')
      if (typeof originalCallback === 'function') {
        originalCallback()
      }
    }

    // Check if CABLES is already loaded
    if ((window as any).CABLES?.patch) {
      patchReady.value = true
      emit('patchReady')
    }
  }
})

defineExpose({ patchReady })
</script>

<style scoped>
#cables-container {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  z-index: 0;
}

#glcanvas {
  width: 100%;
  height: 100%;
}
</style>
