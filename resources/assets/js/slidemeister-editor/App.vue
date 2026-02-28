<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import Moveable from 'vue3-moveable'
import { useEditorStore } from '@/stores/editorStore'
import { useHistory } from '@/composables/useHistory'
import { useMoveable } from '@/composables/useMoveable'
import { useTextEditor } from '@/composables/useTextEditor'
import { useFileDrop } from '@/composables/useFileDrop'
import { useEditorKeyboard } from '@/composables/useEditorKeyboard'
import { useFonts } from '@/composables/useFonts'
import { useHtmlSerializer } from '@/composables/useHtmlSerializer'
import EditorCanvas from '@/components/EditorCanvas.vue'
import ActionsToolbar from '@/components/ActionsToolbar.vue'
import PropertiesPanel from '@/components/PropertiesPanel.vue'
import LayersPanel from '@/components/LayersPanel.vue'
import DropZone from '@/components/DropZone.vue'

const editorStore = useEditorStore()
const history = useHistory(editorStore)
const moveable = useMoveable(editorStore, history.checkpoint)
const textEditor = useTextEditor(editorStore, history.checkpoint)
const fileDrop = useFileDrop(editorStore, history.checkpoint)
const htmlSerializer = useHtmlSerializer(editorStore)
const fonts = useFonts()

const canvasRef = ref<InstanceType<typeof EditorCanvas> | null>(null)

// Get the active element's DOM ref for Moveable
const activeElementRef = computed(() => {
  if (!editorStore.activeElementName || !canvasRef.value) return null
  return canvasRef.value.getElementRef(editorStore.activeElementName)
})

// Element guidelines for snapping (all element DOM refs except active)
const elementGuidelines = computed(() => {
  if (!canvasRef.value) return []
  const refs: HTMLElement[] = []
  for (const entry of editorStore.elementOrder) {
    if (entry.name !== editorStore.activeElementName) {
      const el = canvasRef.value.getElementRef(entry.name)
      if (el) refs.push(el)
    }
  }
  return refs
})

const moveableOpts = computed(() => moveable.moveableOptions.value)

// Keyboard shortcuts
useEditorKeyboard({
  undo: () => history.undo(),
  redo: () => history.redo(),
  save: () => onSave(),
  deleteElement: () => {
    if (editorStore.activeElementName) {
      history.checkpoint()
      editorStore.deleteElement(editorStore.activeElementName)
    }
  },
  cloneElement: () => {
    if (editorStore.activeElementName) {
      history.checkpoint()
      editorStore.cloneElement(editorStore.activeElementName)
    }
  },
  deselect: () => {
    textEditor.stopEditing()
    editorStore.setActiveElement(null)
  },
  isTextEditing: () => textEditor.isEditing.value,
})

// Save handler -- serialize HTML before saving
async function onSave(): Promise<void> {
  const html = htmlSerializer.serializeAll()
  editorStore.isSaving = true
  try {
    const api = (await import('@/composables/useApi')).useApi()
    const defs = editorStore.toDefinitions()
    const payload = {
      name: editorStore.templateName,
      template_for: editorStore.templateType,
      definitions: JSON.stringify(defs),
      cached_html_preview: html,
      cached_html_final: html,
    }
    if (editorStore.templateId !== null) {
      await api.saveTemplate(editorStore.templateId, payload)
    } else {
      const response = await api.createTemplate(payload)
      editorStore.templateId = response.id
    }
    editorStore.isDirty = false
  } finally {
    editorStore.isSaving = false
  }
}

// Load template on mount if TEMPLATE_ID is set
onMounted(async () => {
  fonts.fetchFonts()
  const templateId = window.TEMPLATE_ID
  if (templateId) {
    await editorStore.loadFromApi(templateId)
  }
})

onUnmounted(() => {
  textEditor.destroy()
})
</script>

<template>
  <div
    class="editor-app"
    @dragenter="fileDrop.onDragEnter"
    @dragover="fileDrop.onDragOver"
    @dragleave="fileDrop.onDragLeave"
    @drop="fileDrop.onDrop"
  >
    <ActionsToolbar
      :can-undo="history.canUndo.value"
      :can-redo="history.canRedo.value"
      :checkpoint="history.checkpoint"
      @undo="history.undo"
      @redo="history.redo"
      @save="onSave"
    />

    <div class="editor-main">
      <div class="canvas-area">
        <EditorCanvas ref="canvasRef" />
        <!-- Moveable targets the active element -->
        <Moveable
          v-if="editorStore.activeElementName && moveableOpts && activeElementRef"
          :target="activeElementRef"
          v-bind="moveableOpts"
          :element-guidelines="elementGuidelines"
          @drag-start="moveable.onDragStart"
          @drag="moveable.onDrag"
          @drag-end="moveable.onDragEnd"
          @resize-start="moveable.onResizeStart"
          @resize="moveable.onResize"
          @resize-end="moveable.onResizeEnd"
          @rotate-start="moveable.onRotateStart"
          @rotate="moveable.onRotate"
          @rotate-end="moveable.onRotateEnd"
          @warp-start="moveable.onWarpStart"
          @warp="moveable.onWarp"
          @warp-end="moveable.onWarpEnd"
        />
      </div>

      <div class="sidebar">
        <PropertiesPanel />
        <LayersPanel :checkpoint="history.checkpoint" />
      </div>
    </div>

    <div class="status-bar">
      <span v-if="editorStore.templateType">{{ editorStore.templateType }}</span>
      <span>{{ Object.keys(editorStore.elements).length }} elements</span>
      <span v-if="editorStore.isDirty" class="unsaved">Unsaved changes</span>
    </div>

    <DropZone :visible="fileDrop.isDragging.value" />
  </div>
</template>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html, body, #app {
  width: 100%;
  height: 100%;
  overflow: hidden;
  background: #1a1a1a;
  color: #eee;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  font-size: 13px;
}

.editor-app {
  display: flex;
  flex-direction: column;
  height: 100vh;
}

.editor-main {
  display: flex;
  flex: 1;
  overflow: hidden;
}

.canvas-area {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: auto;
  position: relative;
}

.sidebar {
  width: 320px;
  min-width: 320px;
  background: #1e1e1e;
  border-left: 1px solid #333;
  display: flex;
  flex-direction: column;
  overflow-y: auto;
}

.status-bar {
  display: flex;
  gap: 16px;
  padding: 4px 12px;
  background: #1a1a1a;
  border-top: 1px solid #333;
  font-size: 12px;
  color: #888;
}

.status-bar .unsaved {
  color: #ff9900;
}
</style>
