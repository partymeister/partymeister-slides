<script setup lang="ts">
import { ref, computed, nextTick, watch, onMounted, onUnmounted } from 'vue'
import Moveable from 'vue3-moveable'
import { useEditorStore } from '@/stores/editorStore'
import { useHistory } from '@/composables/useHistory'
import { useMoveable } from '@/composables/useMoveable'
import { useTextEditor } from '@/composables/useTextEditor'
import { useFileDrop } from '@/composables/useFileDrop'
import { useEditorKeyboard } from '@/composables/useEditorKeyboard'
import { useFonts } from '@/composables/useFonts'
import { serializeElements } from '@common/composables/useHtmlSerializer'
import { useTextResize } from '@/composables/useTextResize'
import EditorCanvas from '@/components/EditorCanvas.vue'
import ActionsToolbar from '@/components/ActionsToolbar.vue'
import PropertiesPanel from '@/components/PropertiesPanel.vue'
import LayersPanel from '@/components/LayersPanel.vue'
import MediaPanel from '@/components/MediaPanel.vue'
import DropZone from '@/components/DropZone.vue'
import StartPage from '@/components/StartPage.vue'

const editorMode = window.EDITOR_MODE || 'start'
const entityId = window.ENTITY_ID

const editorStore = useEditorStore()
const history = useHistory(editorStore)
const moveable = useMoveable(editorStore, () => history.checkpoint('Move'))
const textEditor = useTextEditor(editorStore, () => history.checkpoint('Edit text'), (name) => {
  nextTick(() => resizeElementByName(name))
})
const fileDrop = useFileDrop(editorStore, () => history.checkpoint('Drop image'), () => {
  nextTick(() => moveableRef.value?.updateRect())
})
const textResize = useTextResize(editorStore)
const fonts = useFonts()

const canvasRef = ref<InstanceType<typeof EditorCanvas> | null>(null)
const moveableRef = ref<InstanceType<typeof Moveable> | null>(null)

// Saved toast
const showSavedToast = ref(false)

// Snap guides toggle
const showSnapGuides = ref(true)

// Sidebar tab
const sidebarTab = ref<'properties' | 'media'>('properties')

// Helper: resize text for a single element by name
function resizeElementByName(name: string): void {
  if (!canvasRef.value) return
  const container = canvasRef.value.getElementRef(name)
  if (container) textResize.resizeElement(name, container)
}

// Helper: resize all elements
function resizeAllElements(): void {
  if (!canvasRef.value) return
  textResize.resizeAll((name) => canvasRef.value!.getElementRef(name))
}

// Get the active element's DOM ref for Moveable
const activeElementRef = computed(() => {
  if (!editorStore.activeElementName || !canvasRef.value) return null
  return canvasRef.value.getElementRef(editorStore.activeElementName)
})

// Element guidelines for snapping (all element DOM refs except active)
const elementGuidelines = computed(() => {
  if (!canvasRef.value || !showSnapGuides.value) return []
  const refs: HTMLElement[] = []
  for (const entry of editorStore.elementOrder) {
    if (entry.name !== editorStore.activeElementName) {
      const el = canvasRef.value.getElementRef(entry.name)
      if (el) refs.push(el)
    }
  }
  return refs
})

// Canvas center/edge snap guides
const canvasHorizontalGuides = computed(() =>
  showSnapGuides.value ? [0, 270, 540] : [],
)
const canvasVerticalGuides = computed(() =>
  showSnapGuides.value ? [0, 480, 960] : [],
)

const moveableOpts = computed(() => moveable.moveableOptions.value)

// Hide Moveable when text editing is active
const showMoveable = computed(() =>
  editorStore.activeElementName
  && moveableOpts.value
  && activeElementRef.value
  && !textEditor.isEditing.value
)

// Parse translate from matrix transform string
function parseTranslate(transform: string): { x: number; y: number } {
  const match = transform.match(/matrix\(\s*[\d.e+-]+\s*,\s*[\d.e+-]+\s*,\s*[\d.e+-]+\s*,\s*[\d.e+-]+\s*,\s*([\d.e+-]+)\s*,\s*([\d.e+-]+)\s*\)/)
  if (match) return { x: parseFloat(match[1]), y: parseFloat(match[2]) }
  return { x: 0, y: 0 }
}

// Status bar: active element position/size
const activeElementInfo = computed(() => {
  const el = editorStore.activeElement
  if (!el) return null
  const pos = parseTranslate(el.properties.coordinates.transform)
  return {
    x: Math.round(pos.x),
    y: Math.round(pos.y),
    w: el.properties.coordinates.width,
    h: el.properties.coordinates.height,
  }
})

// Handle double-click on element → start tiptap editing (skip image-only elements)
function onElementDblclick(name: string): void {
  if (!name) {
    onStopEditing()
    return
  }
  const el = editorStore.elements[name]
  if (el && (el.properties.image || el.properties.dataUrl) && !el.properties.content) {
    return
  }
  textEditor.startEditing(name)
}

// Stop editing and recalculate text fit
function onStopEditing(): void {
  const editingName = textEditor.editingElementName.value
  textEditor.stopEditing()
  if (editingName) {
    nextTick(() => resizeElementByName(editingName))
  }
}

// After Moveable resize, recalculate text fit
function onResizeEnd(): void {
  moveable.onResizeEnd()
  if (editorStore.activeElementName) {
    nextTick(() => resizeElementByName(editorStore.activeElementName!))
  }
}

// During Moveable resize, live-recalculate text fit
function onResizeLive(e: Parameters<typeof moveable.onResize>[0]): void {
  moveable.onResize(e)
  if (editorStore.activeElementName) {
    resizeElementByName(editorStore.activeElementName)
  }
}

// Keep Moveable in sync whenever the active element's geometry changes (e.g. undo/redo)
watch(
  () => {
    const el = editorStore.activeElement
    if (!el) return null
    const c = el.properties.coordinates
    return `${c.transform}|${c.width}|${c.height}`
  },
  () => {
    nextTick(() => moveableRef.value?.updateRect())
  },
)

// When a property changes in PropertiesPanel, recalculate text if it affects sizing
const TEXT_AFFECTING_PROPS = [
  'properties.fontSize', 'properties.fontFamily', 'properties.fontWeight',
  'properties.fontStretch', 'properties.fontStyle', 'properties.fontKerning',
  'properties.letterSpacing', 'properties.lineHeight', 'properties.textTransform',
  'properties.content',
]
function onPropertyChange(path: string): void {
  if (!editorStore.activeElementName) return
  if (path === 'properties.fontSize') {
    editorStore.updateElementProperty(
      editorStore.activeElementName,
      'properties.calculatedFontSize',
      '',
    )
  }
  if (TEXT_AFFECTING_PROPS.includes(path)) {
    nextTick(() => resizeElementByName(editorStore.activeElementName!))
  }
  nextTick(() => {
    moveableRef.value?.updateRect()
  })
}

// Handle click on Moveable control area → detect double-click
let lastMoveableClickTime = 0
function onMoveableClick(): void {
  const now = Date.now()
  if (now - lastMoveableClickTime < 400 && editorStore.activeElementName) {
    // Skip text editing for image-only elements
    const el = editorStore.elements[editorStore.activeElementName]
    if (el && (el.properties.image || el.properties.dataUrl) && !el.properties.content) {
      lastMoveableClickTime = 0
      return
    }
    textEditor.startEditing(editorStore.activeElementName)
  }
  lastMoveableClickTime = now
}

// Arrow key nudging
let nudgeCheckpointed = false
function onNudge(dx: number, dy: number): void {
  if (!editorStore.activeElementName) return
  const el = editorStore.activeElement
  if (!el || el.properties.locked) return

  if (!nudgeCheckpointed) {
    history.checkpoint('Nudge')
    nudgeCheckpointed = true
    // Reset after a pause so the next nudge series gets its own checkpoint
    setTimeout(() => { nudgeCheckpointed = false }, 500)
  }

  const pos = parseTranslate(el.properties.coordinates.transform)
  const newX = pos.x + dx
  const newY = pos.y + dy
  // Rebuild the matrix with new translate values
  const transform = el.properties.coordinates.transform
  const newTransform = transform.replace(
    /matrix\(([^,]+),([^,]+),([^,]+),([^,]+),[^,]+,[^)]+\)/,
    `matrix($1,$2,$3,$4, ${newX}, ${newY})`,
  )
  editorStore.updateElementProperty(
    editorStore.activeElementName,
    'properties.coordinates.transform',
    newTransform,
  )
  // Also update the DOM element directly for immediate visual feedback
  const domEl = canvasRef.value?.getElementRef(editorStore.activeElementName)
  if (domEl) domEl.style.transform = newTransform
  nextTick(() => moveableRef.value?.updateRect())
}

// Keyboard shortcuts
useEditorKeyboard({
  undo: () => { history.undo(); nextTick(() => moveableRef.value?.updateRect()) },
  redo: () => { history.redo(); nextTick(() => moveableRef.value?.updateRect()) },
  save: () => onSave(),
  cloneElement: () => {
    if (editorStore.activeElementName) {
      history.checkpoint('Clone element')
      editorStore.cloneElement(editorStore.activeElementName)
    }
  },
  deselect: () => {
    if (textEditor.isEditing.value) {
      onStopEditing()
    } else {
      editorStore.setActiveElement(null)
    }
  },
  nudge: onNudge,
  isTextEditing: () => textEditor.isEditing.value,
})

// Save handler
async function onSave(): Promise<void> {
  const html = serializeElements(editorStore.elements)
  editorStore.isSaving = true
  try {
    const api = (await import('@common/composables/useApi')).useApi()
    const defs = editorStore.toDefinitions()

    if (editorStore.editorMode === 'slide') {
      const payload = {
        name: editorStore.templateName,
        definitions: JSON.stringify(defs),
        cached_html_preview: html,
        cached_html_final: html,
      }
      if (editorStore.entityId !== null) {
        await api.saveSlide(editorStore.entityId, payload)
      } else {
        const response = await api.createSlide(payload)
        editorStore.entityId = response.id
        window.location.href = `/slidemeister-editor/slide/${response.id}`
        return
      }
    } else {
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
        window.location.href = `/slidemeister-editor/template/${response.id}`
        return
      }
    }

    editorStore.isDirty = false
    showSavedToast.value = true
    setTimeout(() => { showSavedToast.value = false }, 2000)
  } finally {
    editorStore.isSaving = false
  }
}


// Apply image from media panel to active element (or create new element)
function onApplyImage(url: string): void {
  if (editorStore.activeElementName) {
    history.checkpoint('Set background image')
    editorStore.updateElementProperty(editorStore.activeElementName, 'properties.image', url)
    editorStore.updateElementProperty(editorStore.activeElementName, 'properties.content', '')
  } else {
    history.checkpoint('Add image element')
    const name = editorStore.addElement()
    editorStore.updateElementProperty(name, 'properties.image', url)
    editorStore.updateElementProperty(name, 'properties.content', '')
    editorStore.updateElementProperty(name, 'properties.prettyname', 'Image')
    editorStore.setActiveElement(name)
  }
}

// Delete element from layers panel
function onDeleteElement(name: string): void {
  const el = editorStore.elements[name]
  const label = el?.properties.prettyname || name
  if (!confirm(`Delete "${label}"?`)) return
  history.checkpoint('Delete element')
  editorStore.deleteElement(name)
}

// Update Moveable overlay when window resizes
function onWindowResize(): void {
  moveableRef.value?.updateRect()
}

// Load entity on mount based on EDITOR_MODE
onMounted(async () => {
  window.addEventListener('resize', onWindowResize)
  if (editorMode === 'start') return
  fonts.fetchFonts()
  if (editorMode === 'template' && entityId) {
    await editorStore.loadFromApi(entityId)
  } else if (editorMode === 'create_from_template' && entityId) {
    await editorStore.loadTemplateAsNewSlide(entityId)
  } else if (editorMode === 'slide' && entityId) {
    await editorStore.loadSlideFromApi(entityId)
  }
  nextTick(() => {
    setTimeout(() => {
      resizeAllElements()
      editorStore.isDirty = false
      history.reset()
    }, 100)
  })
})

onUnmounted(() => {
  window.removeEventListener('resize', onWindowResize)
  textEditor.destroy()
})
</script>

<template>
  <StartPage v-if="editorMode === 'start'" />
  <div
    v-else
    class="editor-app"
    @dragenter="fileDrop.onDragEnter"
    @dragover="fileDrop.onDragOver"
    @dragleave="fileDrop.onDragLeave"
    @drop="fileDrop.onDrop"
  >
    <ActionsToolbar
      :can-undo="history.canUndo.value"
      :can-redo="history.canRedo.value"
      :undo-description="history.undoDescription.value"
      :redo-description="history.redoDescription.value"
      :checkpoint="() => history.checkpoint('Toolbar action')"
      :show-snap-guides="showSnapGuides"
      @undo="() => { history.undo(); nextTick(() => moveableRef.value?.updateRect()) }"
      @redo="() => { history.redo(); nextTick(() => moveableRef.value?.updateRect()) }"
      @save="onSave"

      @toggle-snap-guides="showSnapGuides = !showSnapGuides"
    />

    <div class="editor-main">
      <div class="canvas-area">
        <EditorCanvas
          ref="canvasRef"
          :editor="textEditor.editor.value"
          :editing-element-name="textEditor.editingElementName.value"
          :show-snap-guides="showSnapGuides"
          @element-dblclick="onElementDblclick"
          @stop-editing="onStopEditing"
        />
        <Moveable
          ref="moveableRef"
          v-if="showMoveable"
          :target="activeElementRef"
          v-bind="moveableOpts"
          :element-guidelines="elementGuidelines"
          :horizontal-guidelines="canvasHorizontalGuides"
          :vertical-guidelines="canvasVerticalGuides"
          @click="onMoveableClick"
          @drag-start="moveable.onDragStart"
          @drag="moveable.onDrag"
          @drag-end="moveable.onDragEnd"
          @resize-start="moveable.onResizeStart"
          @resize="onResizeLive"
          @resize-end="onResizeEnd"
          @rotate-start="moveable.onRotateStart"
          @rotate="moveable.onRotate"
          @rotate-end="moveable.onRotateEnd"
          @warp-start="moveable.onWarpStart"
          @warp="moveable.onWarp"
          @warp-end="moveable.onWarpEnd"
        />
        <DropZone :visible="fileDrop.isDragging.value" />
      </div>

      <div class="sidebar">
        <div class="sidebar-tabs">
          <button :class="{ active: sidebarTab === 'properties' }" @click="sidebarTab = 'properties'">Properties</button>
          <button :class="{ active: sidebarTab === 'media' }" @click="sidebarTab = 'media'">Media</button>
        </div>
        <div class="sidebar-content">
          <template v-if="sidebarTab === 'properties'">
            <PropertiesPanel :fonts="fonts.availableFonts.value" :checkpoint="(desc: string) => history.checkpoint(desc)" @property-change="onPropertyChange" />
            <LayersPanel :checkpoint="() => history.checkpoint('Reorder layers')" @delete-element="onDeleteElement" />
          </template>
          <template v-else>
            <MediaPanel @apply-image="onApplyImage" />
          </template>
        </div>
      </div>
    </div>

    <div class="status-bar">
      <span v-if="editorStore.templateType">{{ editorStore.templateType }}</span>
      <span>{{ Object.keys(editorStore.elements).length }} elements</span>
      <template v-if="activeElementInfo">
        <span class="coords">X: {{ activeElementInfo.x }} Y: {{ activeElementInfo.y }} W: {{ activeElementInfo.w }} H: {{ activeElementInfo.h }}</span>
      </template>
      <span v-if="history.lastAction.value" class="last-action">{{ history.lastAction.value }}</span>
      <span class="spacer" />
      <span v-if="showSavedToast" class="saved-toast">Saved</span>
      <span v-if="editorStore.isDirty" class="unsaved">Unsaved changes</span>
    </div>

  </div>
</template>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  scrollbar-width: thin;
  scrollbar-color: #555 transparent;
}

::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background: #555;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #777;
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
  overflow: hidden;
  position: relative;
}

.sidebar {
  width: 320px;
  min-width: 320px;
  background: #1e1e1e;
  border-left: 1px solid #333;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.sidebar-tabs {
  display: flex;
  border-bottom: 1px solid #333;
  flex-shrink: 0;
}

.sidebar-tabs button {
  flex: 1;
  padding: 8px 0;
  background: #1e1e1e;
  border: none;
  border-bottom: 2px solid transparent;
  color: #888;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  cursor: pointer;
  transition: color 0.15s, border-color 0.15s;
}

.sidebar-tabs button:hover {
  color: #ccc;
}

.sidebar-tabs button.active {
  color: #4a9eff;
  border-bottom-color: #4a9eff;
}

.sidebar-content {
  flex: 1;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  min-height: 0;
}

.status-bar {
  display: flex;
  gap: 16px;
  padding: 4px 12px;
  background: #1a1a1a;
  border-top: 1px solid #333;
  font-size: 12px;
  color: #888;
  align-items: center;
}

.status-bar .coords {
  font-family: monospace;
  color: #aaa;
}

.status-bar .last-action {
  color: #7aa2f7;
}

.status-bar .spacer {
  flex: 1;
}

.status-bar .saved-toast {
  color: #2a7a3a;
  font-weight: 600;
}

.status-bar .unsaved {
  color: #ff9900;
}
</style>
