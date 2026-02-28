<template>
  <Teleport to="body">
    <div v-if="visible" class="dropzone-overlay">
      <div class="dropzone-content">
        <span>Drop image here</span>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
defineProps<{
  visible: boolean
}>()
</script>

<style scoped>
.dropzone-overlay {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.6);
  pointer-events: none;
}

.dropzone-content {
  padding: 2rem 3rem;
  border: 3px dashed rgba(255, 255, 255, 0.7);
  border-radius: 12px;
  color: #fff;
  font-size: 1.5rem;
  font-weight: 600;
}
</style>
