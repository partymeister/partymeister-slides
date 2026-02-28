import type { SlideElement } from '@/types/editor'
import { useEditorStore } from '@/stores/editorStore'

function escapeAttr(value: string): string {
  return value
    .replace(/&/g, '&amp;')
    .replace(/"/g, '&quot;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
}

export function useHtmlSerializer(editorStore: ReturnType<typeof useEditorStore>) {
  function serializeElement(el: SlideElement): string {
    const p = el.properties
    const c = p.coordinates

    // Build outer div inline styles
    const outerStyles: string[] = [
      `transform: ${c.transform}`,
      `width: ${c.width}px`,
      `height: ${c.height}px`,
      `z-index: ${p.zIndex}`,
      `background-color: ${p.backgroundColor}`,
      `opacity: ${p.opacity}`,
      'display: flex',
      `align-items: ${p.verticalAlign}`,
      'position: absolute',
    ]

    if (p.image) {
      outerStyles.push(`background-image: url(${p.image})`)
      outerStyles.push('background-size: cover')
      outerStyles.push('background-position: center')
    }

    if (p.dataUrl) {
      outerStyles.push(`background-image: url(${p.dataUrl})`)
      outerStyles.push('background-size: cover')
      outerStyles.push('background-position: center')
    }

    // Determine font size: use calculatedFontSize if set and meaningful, otherwise fontSize
    const fontSize =
      p.calculatedFontSize && p.calculatedFontSize !== ''
        ? p.calculatedFontSize.toString().endsWith('px')
          ? p.calculatedFontSize
          : `${p.calculatedFontSize}px`
        : `${p.fontSize}px`

    // Build inner div inline styles
    const innerStyles: string[] = [
      `font-family: ${p.fontFamily}`,
      `font-size: ${fontSize}`,
      `font-kerning: ${p.fontKerning}`,
      `font-weight: ${p.fontWeight}`,
      `font-stretch: ${p.fontStretch}%`,
      `font-style: ${p.fontStyle}`,
      `color: ${p.color}`,
      `text-align: ${p.textAlign}`,
      `line-height: ${p.lineHeight}`,
      `text-shadow: ${p.textShadow}`,
      `text-transform: ${p.textTransform}`,
      'width: 100%',
    ]

    const outerStyleStr = outerStyles.join('; ')
    const innerStyleStr = innerStyles.join('; ')

    const visibility = escapeAttr(p.visibility)
    const prettyname = escapeAttr(p.prettyname)

    return (
      `<div class="moveable" style="${escapeAttr(outerStyleStr)}"` +
      ` data-partymeister-slides-visibility="${visibility}"` +
      ` data-partymeister-slides-prettyname="${prettyname}">` +
      `<div class="medium-editor-element" style="${escapeAttr(innerStyleStr)}">` +
      `${p.content}` +
      `</div>` +
      `</div>`
    )
  }

  function serializeAll(): string {
    return editorStore.sortedElements.map((el) => serializeElement(el)).join('\n')
  }

  return { serializeElement, serializeAll }
}
