<template>
  <div class="editor-canvas" ref="canvasRef">
    <div class="slide-surface" ref="surfaceRef" @mousedown="onSurfaceClick">
      <div
        v-for="entry in editorStore.sortedElements"
        :key="entry.name"
        :ref="(el) => setElementRef(entry.name, el as HTMLElement)"
        class="slide-element moveable"
        :class="{
          active: entry.name === editorStore.activeElementName,
          hover: entry.name === editorStore.hoverElementName,
          locked: entry.properties.locked,
        }"
        :style="elementStyle(entry)"
        :data-partymeister-slides-visibility="entry.properties.visibility"
        :data-partymeister-slides-prettyname="entry.properties.prettyname"
        @mousedown.stop="editorStore.setActiveElement(entry.name)"
        @mouseover="editorStore.setHoverElement(entry.name)"
        @mouseout="editorStore.setHoverElement(null)"
      >
        <div class="element-content medium-editor-element" :style="contentStyle(entry)" v-html="entry.properties.content" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, type CSSProperties } from 'vue'
import { useEditorStore } from '@/stores/editorStore'
import type { SlideElement } from '@/types/editor'

const editorStore = useEditorStore()

const canvasRef = ref<HTMLElement | null>(null)
const surfaceRef = ref<HTMLElement | null>(null)
const elementRefs = new Map<string, HTMLElement>()

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
  return {
    fontFamily: el.properties.fontFamily,
    fontSize: el.properties.fontSize + 'px',
    fontKerning: el.properties.fontKerning as CSSProperties['fontKerning'],
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

function onSurfaceClick(event: MouseEvent): void {
  if (event.target === surfaceRef.value) {
    editorStore.setActiveElement(null)
  }
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
  background: #000;
  border: 1px solid #333;
  overflow: hidden;
}

.slide-element {
  cursor: move;
  box-sizing: border-box;
}

.slide-element.active {
  outline: 2px solid #4a9eff;
}

.slide-element.hover:not(.active) {
  outline: 1px dashed #4a9eff55;
}

.slide-element.locked {
  cursor: default;
}

.element-content {
  box-sizing: border-box;
  word-wrap: break-word;
  overflow: hidden;
}
</style>
