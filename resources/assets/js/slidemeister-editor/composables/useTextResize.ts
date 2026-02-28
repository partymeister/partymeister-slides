import type { useEditorStore } from '@/stores/editorStore'

const MIN_FONT = 5

/**
 * Auto-shrink text to fit its container.
 *
 * `fontSize` in the store is the user-set *maximum*. This composable computes
 * the largest size <= fontSize that still fits inside the element's bounding
 * box, and writes it to `calculatedFontSize`.
 *
 * Works by measuring the real DOM element – call `resizeElement()` after any
 * change to content, font properties, or container dimensions.
 */
export function useTextResize(editorStore: ReturnType<typeof useEditorStore>) {
  /**
   * Recalculate font size for a single element.
   * @param name       element key in the store
   * @param container  the `.slide-element` DOM node (has width/height)
   */
  function resizeElement(name: string, container: HTMLElement): void {
    const el = editorStore.elements[name]
    if (!el) return

    const textDiv = container.querySelector('.element-content') as HTMLElement | null
    if (!textDiv) return

    const maxFontSize = el.properties.fontSize
    const maxWidth = el.properties.coordinates.width
    const maxHeight = el.properties.coordinates.height

    // Start at the user-set max and shrink until it fits
    let size = maxFontSize
    textDiv.style.fontSize = size + 'px'

    while (size > MIN_FONT && textDiv.scrollHeight > maxHeight) {
      size--
      textDiv.style.fontSize = size + 'px'
    }

    // Safety margin for cross-platform rendering differences
    if (size > MIN_FONT && (maxHeight - textDiv.scrollHeight) < 2) {
      size--
      textDiv.style.fontSize = size + 'px'
    }

    editorStore.updateElementProperty(name, 'properties.calculatedFontSize', size + 'px')
  }

  /**
   * Recalculate font size for every element on the canvas.
   * @param getRef  function that returns the DOM node for a given element name
   */
  function resizeAll(getRef: (name: string) => HTMLElement | undefined): void {
    for (const entry of editorStore.elementOrder) {
      const container = getRef(entry.name)
      if (container) {
        resizeElement(entry.name, container)
      }
    }
  }

  return { resizeElement, resizeAll }
}
