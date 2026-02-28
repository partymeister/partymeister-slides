<script setup lang="ts">
import { useEditorStore } from '@/stores/editorStore'
import type { TemplateType } from '@/types/editor'

const props = defineProps<{
  canUndo: boolean
  canRedo: boolean
  undoDescription: string
  redoDescription: string
  checkpoint: () => void
  showSnapGuides: boolean
}>()

const emit = defineEmits<{
  undo: []
  redo: []
  save: []
  preview: []
  'toggle-snap-guides': []
}>()

const editorStore = useEditorStore()

const templateTypes: TemplateType[] = [
  'basic',
  'coming_up',
  'now',
  'end',
  'competition',
  'competition_entry_1',
  'timetable',
  'participants',
  'prizegiving',
  'comments',
  'end_of_pg',
]

function formatType(t: string): string {
  return t
    .split('_')
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ')
}

function onAdd(): void {
  props.checkpoint()
  editorStore.addElement()
}

function onClone(): void {
  if (!editorStore.activeElementName) return
  props.checkpoint()
  editorStore.cloneElement(editorStore.activeElementName)
}

function onDelete(): void {
  if (!editorStore.activeElementName) return
  const el = editorStore.elements[editorStore.activeElementName]
  const label = el?.properties.prettyname || editorStore.activeElementName
  if (!confirm(`Delete "${label}"?`)) return
  props.checkpoint()
  editorStore.deleteElement(editorStore.activeElementName)
}
</script>

<template>
  <div class="actions-toolbar">
    <!-- Template metadata -->
    <div class="toolbar-group">
      <input
        type="text"
        v-model="editorStore.templateName"
        placeholder="Template name"
        class="toolbar-input name-input"
        @input="editorStore.isDirty = true"
      />
      <select v-model="editorStore.templateType" class="toolbar-select" @change="editorStore.isDirty = true">
        <option v-for="t in templateTypes" :key="t" :value="t">{{ formatType(t) }}</option>
      </select>
    </div>

    <div class="toolbar-separator" />

    <!-- Element actions -->
    <div class="toolbar-group">
      <button @click="onAdd" class="toolbar-btn" title="Add Element">+ Add</button>
      <button @click="onClone" class="toolbar-btn" :disabled="!editorStore.activeElementName" title="Clone Element (Ctrl+D)">Clone</button>
      <button @click="onDelete" class="toolbar-btn danger" :disabled="!editorStore.activeElementName" title="Delete Element">Delete</button>
    </div>

    <div class="toolbar-separator" />

    <!-- Undo/Redo -->
    <div class="toolbar-group">
      <button @click="$emit('undo')" class="toolbar-btn" :disabled="!canUndo" :title="canUndo ? `Undo: ${undoDescription} (Ctrl+Z)` : 'Nothing to undo'">Undo</button>
      <button @click="$emit('redo')" class="toolbar-btn" :disabled="!canRedo" :title="canRedo ? `Redo: ${redoDescription} (Ctrl+Shift+Z)` : 'Nothing to redo'">Redo</button>
    </div>

    <div class="toolbar-separator" />

    <!-- View -->
    <div class="toolbar-group">
      <button
        @click="$emit('toggle-snap-guides')"
        class="toolbar-btn"
        :class="{ toggled: showSnapGuides }"
        title="Toggle snap guides"
      >Snap</button>
      <button @click="$emit('preview')" class="toolbar-btn" title="Open preview in new tab">Preview</button>
    </div>

    <div class="toolbar-separator" />

    <!-- Save -->
    <div class="toolbar-group">
      <button @click="$emit('save')" class="toolbar-btn primary" :disabled="editorStore.isSaving" title="Save (Ctrl+S)">
        {{ editorStore.isSaving ? 'Saving...' : 'Save' }}
      </button>
      <span v-if="editorStore.isDirty" class="dirty-indicator" title="Unsaved changes">*</span>
    </div>
  </div>
</template>

<style scoped>
.actions-toolbar {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  background: #1e1e1e;
  border-bottom: 1px solid #333;
  flex-shrink: 0;
}

.toolbar-group {
  display: flex;
  align-items: center;
  gap: 4px;
}

.toolbar-separator {
  width: 1px;
  height: 24px;
  background: #444;
}

.toolbar-input, .toolbar-select {
  background: #2a2a2a;
  border: 1px solid #444;
  color: #eee;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 13px;
}

.name-input {
  width: 200px;
}

.toolbar-btn {
  background: #2a2a2a;
  border: 1px solid #444;
  color: #eee;
  padding: 4px 12px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
}

.toolbar-btn:hover:not(:disabled) {
  background: #3a3a3a;
}

.toolbar-btn:disabled {
  opacity: 0.4;
  cursor: default;
}

.toolbar-btn.primary {
  background: #1a5a2a;
  border-color: #2a7a3a;
}

.toolbar-btn.primary:hover:not(:disabled) {
  background: #2a7a3a;
}

.toolbar-btn.danger:hover:not(:disabled) {
  background: #5a1a1a;
}

.toolbar-btn.toggled {
  background: #1a3a5a;
  border-color: #4a9eff;
  color: #4a9eff;
}

.dirty-indicator {
  color: #ff9900;
  font-size: 18px;
  font-weight: bold;
}
</style>
