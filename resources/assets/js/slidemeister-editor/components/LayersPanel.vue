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
        <div class="layer-actions">
          <span v-if="isLocked(entry.name)" class="lock-icon" title="Locked">&#x1F512;</span>
          <button
            class="delete-btn"
            title="Delete element"
            @click.stop="$emit('delete-element', entry.name)"
          >&times;</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'
import Sortable from 'sortablejs'
import type { SortableEvent } from 'sortablejs'
import { useEditorStore } from '@/stores/editorStore'
import type { ElementOrder } from '@common/types/editor'

const props = defineProps<{
  checkpoint: () => void
}>()

defineEmits<{
  'delete-element': [name: string]
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
    handle: '.layer-name',
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

watch(
  () => editorStore.elementOrder.length,
  () => {
    destroySortable()
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
  /* Show ~5 layer items max before scrolling */
  max-height: 190px;
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
  overflow-y: auto;
  min-height: 0;
}

.panel-title {
  font-size: 12px;
  text-transform: uppercase;
  color: #888;
  margin: 0;
  padding: 8px 8px 8px;
  position: sticky;
  top: 0;
  background: #1e1e1e;
  z-index: 1;
}

.layer-list {
  display: flex;
  flex-direction: column;
  gap: 2px;
  padding: 0 8px 8px;
}

.layer-item {
  padding: 6px 8px;
  border-radius: 4px;
  cursor: pointer;
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

.layer-name {
  cursor: grab;
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.layer-actions {
  display: flex;
  align-items: center;
  gap: 4px;
  flex-shrink: 0;
}

.lock-icon {
  font-size: 12px;
}

.delete-btn {
  background: none;
  border: none;
  color: #666;
  font-size: 16px;
  cursor: pointer;
  padding: 0 2px;
  line-height: 1;
  border-radius: 2px;
}

.delete-btn:hover {
  color: #ff4444;
  background: rgba(255, 68, 68, 0.1);
}

.sortable-ghost {
  opacity: 0.4;
}
</style>
