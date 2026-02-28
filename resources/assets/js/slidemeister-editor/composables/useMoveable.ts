import { computed } from 'vue'
import type { useEditorStore } from '@/stores/editorStore'

export function useMoveable(
  editorStore: ReturnType<typeof useEditorStore>,
  checkpoint: () => void,
) {
  const moveableOptions = computed(() => {
    const el = editorStore.activeElement
    if (!el) return null
    return {
      draggable: !el.properties.locked,
      resizable: !el.properties.locked && el.properties.resizable,
      rotatable: !el.properties.locked,
      warpable: !el.properties.locked && el.properties.warpable,
      snappable: el.properties.snapping,
      snapThreshold: 10,
      origin: false,
      keepRatio: false,
    }
  })

  function updateTransform(name: string, transform: string): void {
    editorStore.updateElementProperty(name, 'properties.coordinates.transform', transform)
  }

  // ── Drag ──

  function onDragStart(): void {
    checkpoint()
  }

  function onDrag(e: { target: HTMLElement | SVGElement; transform: string }): void {
    const name = editorStore.activeElementName
    if (!name) return
    ;(e.target as HTMLElement).style.transform = e.transform
    updateTransform(name, e.transform)
  }

  function onDragEnd(): void {
    // no-op; checkpoint was taken on start
  }

  // ── Resize ──

  function onResizeStart(): void {
    checkpoint()
  }

  function onResize(e: {
    target: HTMLElement | SVGElement
    width: number
    height: number
    drag: { transform: string }
  }): void {
    const name = editorStore.activeElementName
    if (!name) return
    const target = e.target as HTMLElement
    target.style.width = `${e.width}px`
    target.style.height = `${e.height}px`
    target.style.transform = e.drag.transform
    editorStore.updateElementProperty(name, 'properties.coordinates.width', e.width)
    editorStore.updateElementProperty(name, 'properties.coordinates.height', e.height)
    updateTransform(name, e.drag.transform)
  }

  function onResizeEnd(): void {
    // no-op
  }

  // ── Rotate ──

  function onRotateStart(): void {
    checkpoint()
  }

  function onRotate(e: { target: HTMLElement | SVGElement; transform: string }): void {
    const name = editorStore.activeElementName
    if (!name) return
    ;(e.target as HTMLElement).style.transform = e.transform
    updateTransform(name, e.transform)
  }

  function onRotateEnd(): void {
    // no-op
  }

  // ── Warp ──

  function onWarpStart(): void {
    checkpoint()
  }

  function onWarp(e: { target: HTMLElement | SVGElement; transform: string }): void {
    const name = editorStore.activeElementName
    if (!name) return
    ;(e.target as HTMLElement).style.transform = e.transform
    updateTransform(name, e.transform)
  }

  function onWarpEnd(): void {
    // no-op
  }

  return {
    moveableOptions,
    onDragStart,
    onDrag,
    onDragEnd,
    onResizeStart,
    onResize,
    onResizeEnd,
    onRotateStart,
    onRotate,
    onRotateEnd,
    onWarpStart,
    onWarp,
    onWarpEnd,
  }
}
