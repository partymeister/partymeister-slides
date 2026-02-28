<template>
  <Transition name="slide-down">
    <div
      v-if="connectionStore.error"
      class="connection-status connection-error"
    >
      {{ connectionStore.error }}
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { useConnectionStore } from '@/stores/connectionStore'

const connectionStore = useConnectionStore()
</script>

<style scoped>
.connection-status {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 9999;
  padding: 8px 16px;
  text-align: center;
  font-family: monospace;
  font-size: 14px;
}

.connection-error {
  background: rgba(220, 38, 38, 0.9);
  color: white;
}

.slide-down-enter-active,
.slide-down-leave-active {
  transition: transform 0.3s ease;
}

.slide-down-enter-from,
.slide-down-leave-to {
  transform: translateY(-100%);
}
</style>
