import { ref } from 'vue'
import type { SiegmeisterBarData } from '@/types/playlist'

function clamp(value: number, min: number, max: number): number {
  return Math.max(min, Math.min(max, value))
}

const BASE_WIDTH = 960
const BASE_HEIGHT = 540

export function useSiegmeister(
  barColor: () => string,
  blinkColor: () => string,
  onComplete?: () => void,
) {
  const isAnimating = ref(false)
  let animationFrameId: number | null = null

  function renderBars(
    metadata: SiegmeisterBarData[],
    zoom: number,
    container: HTMLElement,
  ): HTMLElement[] {
    const wrapper = document.createElement('div')
    wrapper.className = 'slidemeister-bar-wrapper'
    wrapper.id = 'slidemeister-bar-wrapper'

    const barElements: HTMLElement[] = []

    for (let i = 0; i < metadata.length; i++) {
      const e = metadata[i]
      const bar = document.createElement('div')
      const left = Number((e.x1 * BASE_WIDTH * zoom).toFixed(2))
      const top = Number((e.y1 * BASE_HEIGHT * zoom).toFixed(2))
      const height = Number(((e.y2 - e.y1) * BASE_HEIGHT * zoom).toFixed(2))

      bar.id = `bar-${i}`
      bar.style.left = `${left}px`
      bar.style.top = `${top}px`
      bar.style.width = '0px'
      bar.style.height = `${height}px`
      bar.className = 'slidemeister-bars active'
      bar.style.backgroundColor = barColor()

      wrapper.appendChild(bar)
      barElements.push(bar)
    }

    container.appendChild(wrapper)
    return barElements
  }

  function animateBars(
    metadata: SiegmeisterBarData[],
    barElements: HTMLElement[],
    zoom: number,
  ): void {
    isAnimating.value = true
    let frame = 0

    function step() {
      frame++

      for (let i = 0; i < metadata.length; i++) {
        const e = metadata[i]
        const time = frame / 240

        const t = time + 0.25 * 0.5 * Math.sin(e.x2 * 2000) * (4 * (-time * time + time))
        const w = clamp(t, 0, e.x2)

        const width = Number(((w - e.x1) * BASE_WIDTH * zoom).toFixed(2))
        const targetWidth = Number(((e.x2 - e.x1) * BASE_WIDTH * zoom).toFixed(2))
        const clampedWidth = Math.min(width, targetWidth)

        if (barElements[i]) {
          barElements[i].style.width = `${Math.max(0, clampedWidth)}px`
        }
      }

      if (frame >= 240) {
        finishAnimation(metadata, barElements)
        return
      }

      animationFrameId = requestAnimationFrame(step)
    }

    animationFrameId = requestAnimationFrame(step)
  }

  function finishAnimation(
    metadata: SiegmeisterBarData[],
    barElements: HTMLElement[],
  ): void {
    // Sort bars by x2 descending
    const indexed = metadata.map((m, i) => ({ ...m, index: i }))
    indexed.sort((a, b) => b.x2 - a.x2)

    // Find top 3 unique x2 values and their bar indices
    const blinkingIndices: number[] = []
    const uniqueValues: number[] = []

    for (const entry of indexed) {
      if (uniqueValues.length < 3 || uniqueValues.includes(entry.x2)) {
        blinkingIndices.push(entry.index)
        if (!uniqueValues.includes(entry.x2)) {
          uniqueValues.push(entry.x2)
        }
      }
    }

    // Apply blink styling
    for (const idx of blinkingIndices) {
      if (barElements[idx]) {
        barElements[idx].style.backgroundColor = blinkColor()
        barElements[idx].classList.add('blink')
      }
    }

    isAnimating.value = false
    animationFrameId = null

    if (onComplete) {
      onComplete()
    }
  }

  function clearBars(container: HTMLElement): void {
    if (animationFrameId !== null) {
      cancelAnimationFrame(animationFrameId)
      animationFrameId = null
    }
    isAnimating.value = false

    const wrapper = container.querySelector('#slidemeister-bar-wrapper')
    if (wrapper) {
      wrapper.remove()
    }
  }

  return { renderBars, animateBars, clearBars, isAnimating }
}
