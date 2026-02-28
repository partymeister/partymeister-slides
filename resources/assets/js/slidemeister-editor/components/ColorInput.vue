<template>
  <div class="color-input">
    <input
      type="color"
      :value="modelValue"
      @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
    />
    <input
      type="text"
      :value="modelValue"
      @change="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
      class="color-text"
    />
    <label v-if="label" class="color-label">{{ label }}</label>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  modelValue: string
  label?: string
}>()

defineEmits<{
  'update:modelValue': [value: string]
}>()
</script>

<style scoped>
.color-input {
  display: flex;
  align-items: center;
  gap: 6px;
}

.color-input input[type='color'] {
  width: 28px;
  height: 28px;
  padding: 0;
  border: 1px solid #555;
  border-radius: 4px;
  background: none;
  cursor: pointer;
  flex-shrink: 0;
}

.color-input input[type='color']::-webkit-color-swatch-wrapper {
  padding: 2px;
}

.color-input input[type='color']::-webkit-color-swatch {
  border: none;
  border-radius: 2px;
}

.color-text {
  flex: 1;
  min-width: 0;
  padding: 4px 6px;
  background: #2a2a2a;
  border: 1px solid #555;
  border-radius: 4px;
  color: #ddd;
  font-size: 12px;
  font-family: monospace;
}

.color-text:focus {
  outline: none;
  border-color: #7aa2f7;
}

.color-label {
  font-size: 12px;
  color: #999;
  white-space: nowrap;
}
</style>
