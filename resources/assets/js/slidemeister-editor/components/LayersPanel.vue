<template>
  <div class="layers-panel">
    <h3 class="panel-title">Layers</h3>
    <div ref="listRef" class="layer-list">
      <div
        v-for="entry in editorStore.elementOrder"
        :key="entry.name"
        class="layer-item"
        :class="{
          active: entry.name === editorStore.activeElementName,
          hover: entry.name === editorStore.hoverElementName,
        }"
        :data-name="entry.name"
        @click="editorStore.setActiveElement(entry.name)"
        @mouseover="editorStore.setHoverElement(entry.name)"
        @mouseout="editorStore.setHoverElement(null)"
      >
        <span class="layer-name">{{ getLabel(entry.name) }}</span>
        <span v-if="isLocked(entry.name)" class="lock-icon">L</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'
import Sortable from 'sortablejs'
import type { SortableEvent } from 'sortablejs'
import { useEditorStore } from '@/stores/editorStore'
import type { ElementOrder } from '@/types/editor'

const props = defineProps<{
  checkpoint: () => void
}>()

const editorStore = useEditorStore()
const listRef = ref<HTMLElement | null>(null)
let sortableInstance: Sortable | null = null

function getLabel(name: string): string {
  const el = editorStore.elements[name]
  if (el?.properties.prettyname) return el.properties.prettyname
  return name
}

function isLocked(name: string): boolean {
  const el = editorStore.elements[name]
  return el?.properties.locked ?? false
}

function initSortable() {
  if (!listRef.value) return
  sortableInstance = Sortable.create(listRef.value, {
    animation: 150,
    ghostClass: 'sortable-ghost',
    onEnd(_evt: SortableEvent) {
      const children = listRef.value?.children
      if (!children) return
      const newOrder: ElementOrder[] = []
      for (let i = 0; i < children.length; i++) {
        const name = (children[i] as HTMLElement).dataset.name
        if (name) {
          newOrder.push({ name, zIndex: 0 })
        }
      }
      props.checkpoint()
      editorStore.reorderElements(newOrder)
    },
  })
}

function destroySortable() {
  if (sortableInstance) {
    sortableInstance.destroy()
    sortableInstance = null
  }
}

onMounted(() => {
  initSortable()
})

// Reinitialize sortable when the element order changes (e.g. add/delete element)
// so that SortableJS stays in sync with the DOM
watch(
  () => editorStore.elementOrder.length,
  () => {
    destroySortable()
    // Wait for Vue to re-render the v-for list before re-initializing
    setTimeout(() => initSortable(), 0)
  },
)

onBeforeUnmount(() => {
  destroySortable()
})
</script>

<style scoped>
.layers-panel {
  border-top: 1px solid #333;
  padding: 8px;
}

.panel-title {
  font-size: 12px;
  text-transform: uppercase;
  color: #888;
  margin: 0 0 8px;
}

.layer-list {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.layer-item {
  padding: 6px 8px;
  border-radius: 4px;
  cursor: grab;
  font-size: 13px;
  color: #ccc;
  display: flex;
  justify-content: space-between;
  align-items: center;
  user-select: none;
}

.layer-item:hover,
.layer-item.hover {
  background: #2a2a2a;
}

.layer-item.active {
  background: #1a3a5a;
  color: #fff;
}

.lock-icon {
  font-size: 11px;
  color: #666;
}

.sortable-ghost {
  opacity: 0.4;
}
</style>
