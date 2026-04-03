<template>
  <div class="editor-canvas" ref="canvasRef" @mousedown.self="onCanvasClick">
    <div class="slide-surface" ref="surfaceRef" @mousedown.self="onSurfaceClick">
      <!-- Snap guide lines (center only — edges are the surface border) -->
      <template v-if="showSnapGuides">
        <div class="snap-guide snap-guide-h" style="top: 270px" />
        <div class="snap-guide snap-guide-v" style="left: 480px" />
      </template>
      <div
        v-for="entry in editorStore.sortedElements"
        :key="entry.name"
        :ref="(el) => setElementRef(entry.name, el as HTMLElement)"
        class="slide-element moveable"
        :class="{
          active: entry.name === editorStore.activeElementName,
          hover: entry.name === editorStore.hoverElementName,
          locked: entry.properties.locked,
          editing: entry.name === editingElementName,
        }"
        :style="elementStyle(entry)"
        :data-partymeister-slides-visibility="entry.properties.visibility"
        :data-partymeister-slides-prettyname="entry.properties.prettyname"
        @mousedown.stop="onElementMousedown(entry.name)"
        @mouseover="editorStore.setHoverElement(entry.name)"
        @mouseout="editorStore.setHoverElement(null)"
      >
        <EditorContent
          v-if="editor && entry.name === editingElementName"
          :editor="editor"
          class="element-content medium-editor-element"
          :style="contentStyle(entry)"
        />
        <div
          v-else
          class="element-content medium-editor-element"
          :style="contentStyle(entry)"
          v-html="entry.properties.content"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, type CSSProperties } from 'vue'
import { EditorContent, type Editor } from '@tiptap/vue-3'
import { useEditorStore } from '@/stores/editorStore'
import type { SlideElement } from '@common/types/editor'

const props = defineProps<{
  editor: Editor | null
  editingElementName: string | null
  showSnapGuides: boolean
}>()

const emit = defineEmits<{
  'element-dblclick': [name: string]
  'stop-editing': []
}>()

const editorStore = useEditorStore()

const canvasRef = ref<HTMLElement | null>(null)
const surfaceRef = ref<HTMLElement | null>(null)
const elementRefs = reactive(new Map<string, HTMLElement>())

// Track clicks for double-click detection (works even when Moveable steals the 2nd click)
let lastClickName = ''
let lastClickTime = 0

function setElementRef(name: string, el: HTMLElement | null): void {
  if (el) {
    elementRefs.set(name, el)
  } else {
    elementRefs.delete(name)
  }
}

function getElementRef(name: string): HTMLElement | undefined {
  return elementRefs.get(name)
}

function onElementMousedown(name: string): void {
  // If we're editing a different element, stop editing first
  if (props.editingElementName && name !== props.editingElementName) {
    emit('stop-editing')
  }

  const now = Date.now()
  if (name === lastClickName && now - lastClickTime < 400) {
    emit('element-dblclick', name)
    lastClickName = ''
    lastClickTime = 0
  } else {
    lastClickName = name
    lastClickTime = now
  }
  editorStore.setActiveElement(name)
}

function elementStyle(el: SlideElement): CSSProperties {
  const style: CSSProperties = {
    position: 'absolute',
    transform: el.properties.coordinates.transform,
    width: el.properties.coordinates.width + 'px',
    height: el.properties.coordinates.height + 'px',
    zIndex: el.properties.zIndex,
    backgroundColor: el.properties.backgroundColor,
    opacity: el.properties.opacity,
    display: 'flex',
    alignItems: el.properties.verticalAlign,
  }

  const bgImage = el.properties.image || el.properties.dataUrl
  if (bgImage) {
    style.backgroundImage = 'url(' + bgImage + ')'
    style.backgroundSize = 'cover'
    style.backgroundPosition = 'center'
  }

  return style
}

function contentStyle(el: SlideElement): CSSProperties {
  // Use calculatedFontSize (shrunk-to-fit) if available, otherwise the user-set max
  const fontSize = el.properties.calculatedFontSize && el.properties.calculatedFontSize !== ''
    ? (el.properties.calculatedFontSize.toString().endsWith('px')
      ? el.properties.calculatedFontSize
      : el.properties.calculatedFontSize + 'px')
    : el.properties.fontSize + 'px'

  return {
    fontFamily: el.properties.fontFamily,
    fontSize,
    fontKerning: el.properties.fontKerning as CSSProperties['fontKerning'],
    letterSpacing: el.properties.letterSpacing,
    fontWeight: el.properties.fontWeight,
    fontStretch: el.properties.fontStretch + '%',
    fontStyle: el.properties.fontStyle,
    color: el.properties.color,
    textAlign: el.properties.textAlign as CSSProperties['textAlign'],
    lineHeight: el.properties.lineHeight,
    textShadow: el.properties.textShadow,
    textTransform: el.properties.textTransform as CSSProperties['textTransform'],
    width: '100%',
  }
}

function onSurfaceClick(): void {
  if (props.editingElementName) {
    emit('stop-editing')
  }
  editorStore.setActiveElement(null)
}

function onCanvasClick(): void {
  if (props.editingElementName) {
    emit('stop-editing')
  }
  editorStore.setActiveElement(null)
}

defineExpose({
  canvasRef,
  surfaceRef,
  getElementRef,
})
</script>

<style scoped>
.editor-canvas {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: auto;
  background: #1a1a1a;
}

.slide-surface {
  position: relative;
  width: 960px;
  height: 540px;
  min-width: 960px;
  min-height: 540px;
  background-color: #fff;
  background-image:
    linear-gradient(45deg, #e0e0e0 25%, transparent 25%),
    linear-gradient(-45deg, #e0e0e0 25%, transparent 25%),
    linear-gradient(45deg, transparent 75%, #e0e0e0 75%),
    linear-gradient(-45deg, transparent 75%, #e0e0e0 75%);
  background-size: 20px 20px;
  background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
  border: 1px solid #333;
  overflow: hidden;
}

.slide-element {
  cursor: move;
  box-sizing: border-box;
}

.slide-element.active {
  /* Moveable provides the selection UI; no extra outline needed */
}

.slide-element.hover:not(.active) {
  outline: 2px dashed #4a9effaa;
}

.slide-element.locked {
  cursor: default;
}

.slide-element.editing {
  outline: 2px solid #ff9900;
  z-index: 9999 !important;
}

.element-content {
  box-sizing: border-box;
  word-wrap: break-word;
  overflow: hidden;
  min-height: 100%;
}

/* Ensure empty <p> tags have line height in both edit and display mode */
.element-content p:empty,
.element-content p:has(> br:only-child) {
  min-height: 1em;
}

.element-content p {
  margin: 0;
  padding: 0;
}

/* EditorContent wrapper must not add extra spacing */
.slide-element.editing > :deep(div) {
  width: 100%;
}

/* Override ProseMirror/tiptap defaults and inherit element styling */
.slide-element.editing :deep(.tiptap),
.slide-element.editing :deep(.ProseMirror) {
  outline: none !important;
  width: 100%;
  cursor: text;
  padding: 0 !important;
  margin: 0 !important;
  border: none !important;
  white-space: normal;
  font: inherit;
  color: inherit;
  text-align: inherit;
  line-height: inherit;
  text-shadow: inherit;
  text-transform: inherit;
}

.slide-element.editing :deep(.ProseMirror p) {
  margin: 0;
  padding: 0;
  line-height: inherit;
  font-size: inherit;
}

/* ProseMirror adds <br class="ProseMirror-trailingBreak"> in empty paragraphs
   to give them height for cursor placement. Keep them visible so Enter
   creates visible empty lines and users can type in empty elements. */

.slide-element.editing :deep(.ProseMirror h1),
.slide-element.editing :deep(.ProseMirror h2),
.slide-element.editing :deep(.ProseMirror h3),
.slide-element.editing :deep(.ProseMirror h4) {
  margin: 0;
  padding: 0;
  line-height: inherit;
  font-size: inherit;
  font-weight: inherit;
}

.snap-guide {
  position: absolute;
  pointer-events: none;
  z-index: 9998;
}

.snap-guide-h {
  left: 0;
  right: 0;
  height: 0;
  border-top: 1px dashed rgba(74, 158, 255, 0.4);
}

.snap-guide-v {
  top: 0;
  bottom: 0;
  width: 0;
  border-left: 1px dashed rgba(74, 158, 255, 0.4);
}
</style>
