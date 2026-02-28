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
    windowWidth?: number,
    windowHeight?: number,
  ): HTMLElement[] {
    const wrapper = document.createElement('div')
    wrapper.className = 'slidemeister-bar-wrapper'
    wrapper.id = 'slidemeister-bar-wrapper'

    // Apply the same centering offset as the slide layer
    if (windowWidth !== undefined && windowHeight !== undefined) {
      const scaledW = BASE_WIDTH * zoom
      const scaledH = BASE_HEIGHT * zoom
      const offsetX = (windowWidth - scaledW) / 2
      const offsetY = (windowHeight - scaledH) / 2
      wrapper.style.position = 'absolute'
      wrapper.style.left = `${offsetX}px`
      wrapper.style.top = `${offsetY}px`
    }

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

    // Generate per-bar wobble parameters
    // We vary the speed (derivative) rather than the position, so bars
    // only slow down or speed up — never retract.
    // Each bar gets a random easing curve built from sine harmonics.
    const wobbles = metadata.map(() => ({
      freq1: 1.5 + Math.random() * 2,
      freq2: 2.5 + Math.random() * 2,
      phase1: Math.random() * Math.PI * 2,
      phase2: Math.random() * Math.PI * 2,
      amp: 0.12 + Math.random() * 0.15,
    }))

    // Precompute a monotonic progress curve per bar (cumulative speed variation)
    // This guarantees bars never go backwards.
    const TOTAL_FRAMES = 240
    const progressCurves: number[][] = wobbles.map((wb) => {
      const speeds: number[] = []
      let total = 0
      for (let f = 1; f <= TOTAL_FRAMES; f++) {
        const t = f / TOTAL_FRAMES
        // Envelope fades wobble out near start and end
        const envelope = Math.sin(Math.PI * t)
        const variation = 1 + wb.amp * envelope * (
          0.6 * Math.sin(wb.freq1 * Math.PI * 2 * t + wb.phase1) +
          0.4 * Math.sin(wb.freq2 * Math.PI * 2 * t + wb.phase2)
        )
        // Speed is always positive (1 + small wobble), so progress is monotonic
        speeds.push(Math.max(0.3, variation))
        total += Math.max(0.3, variation)
      }
      // Normalize so cumulative sum reaches exactly 1.0 at frame 240
      let cumulative = 0
      return speeds.map((s) => {
        cumulative += s / total
        return cumulative
      })
    })

    // Find the longest bar to normalize the "race" feel
    const maxX2 = Math.max(...metadata.map(e => e.x2))

    function step() {
      frame++

      for (let i = 0; i < metadata.length; i++) {
        const e = metadata[i]
        // Monotonic progress 0..1, guaranteed to reach 1.0 at frame 240
        const progress = progressCurves[i][frame - 1]

        // Blend between "all bars race to the same visual width" (early)
        // and "bars reveal their true length" (late).
        // revealCurve: 0 at start -> 1 at end, with late bias (cubic)
        const revealCurve = progress * progress * progress

        // "Equal race" target: all bars grow to maxX2 visually
        const equalWidth = progress * maxX2
        // "True" target: bar grows to its actual x2
        const trueWidth = progress * e.x2

        // Blend: early on bars look neck-and-neck, differences emerge late
        const w = equalWidth + revealCurve * (trueWidth - equalWidth)

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
