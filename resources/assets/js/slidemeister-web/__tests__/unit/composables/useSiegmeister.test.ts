import { useSiegmeister } from '@/composables/useSiegmeister'
import type { SiegmeisterBarData } from '@/types/playlist'

describe('useSiegmeister', () => {
  let container: HTMLElement
  let siegmeister: ReturnType<typeof useSiegmeister>
  const onComplete = vi.fn()

  const barColor = () => '#00ff00'
  const blinkColor = () => '#ff0000'

  const sampleBars: SiegmeisterBarData[] = [
    { x1: 0.1, y1: 0.2, x2: 0.5, y2: 0.4 },
    { x1: 0.1, y1: 0.5, x2: 0.8, y2: 0.7 },
    { x1: 0.1, y1: 0.8, x2: 0.3, y2: 0.9 },
  ]

  beforeEach(() => {
    vi.clearAllMocks()
    container = document.createElement('div')
    document.body.appendChild(container)
    siegmeister = useSiegmeister(barColor, blinkColor, onComplete)
  })

  afterEach(() => {
    siegmeister.clearBars(container)
    container.remove()
  })

  describe('renderBars', () => {
    it('creates correct number of bar elements', () => {
      const bars = siegmeister.renderBars(sampleBars, 1, container)

      expect(bars).toHaveLength(3)
      const wrapper = container.querySelector('#slidemeister-bar-wrapper')
      expect(wrapper).not.toBeNull()
      expect(wrapper!.children).toHaveLength(3)
    })

    it('positions bars correctly based on coordinates and zoom', () => {
      const zoom = 2
      const bars = siegmeister.renderBars(sampleBars, zoom, container)

      // Bar 0: x1=0.1, y1=0.2, y2=0.4
      // left = 0.1 * 960 * 2 = 192
      // top = 0.2 * 540 * 2 = 216
      // height = (0.4 - 0.2) * 540 * 2 = 216
      expect(bars[0].style.left).toBe('192px')
      expect(bars[0].style.top).toBe('216px')
      expect(bars[0].style.height).toBe('216px')
      expect(bars[0].style.width).toBe('0px')
    })

    it('sets bar background color from barColor getter', () => {
      const bars = siegmeister.renderBars(sampleBars, 1, container)

      expect(bars[0].style.backgroundColor).toBe('#00ff00')
    })

    it('assigns correct IDs to bars', () => {
      const bars = siegmeister.renderBars(sampleBars, 1, container)

      expect(bars[0].id).toBe('bar-0')
      expect(bars[1].id).toBe('bar-1')
      expect(bars[2].id).toBe('bar-2')
    })

    it('applies correct CSS classes', () => {
      const bars = siegmeister.renderBars(sampleBars, 1, container)

      expect(bars[0].className).toBe('slidemeister-bars active')
    })
  })

  describe('clearBars', () => {
    it('removes all bar elements from the container', () => {
      siegmeister.renderBars(sampleBars, 1, container)

      expect(container.querySelector('#slidemeister-bar-wrapper')).not.toBeNull()

      siegmeister.clearBars(container)

      expect(container.querySelector('#slidemeister-bar-wrapper')).toBeNull()
    })

    it('does not throw when no bars exist', () => {
      expect(() => siegmeister.clearBars(container)).not.toThrow()
    })

    it('sets isAnimating to false', () => {
      siegmeister.clearBars(container)

      expect(siegmeister.isAnimating.value).toBe(false)
    })
  })

  describe('animateBars', () => {
    let rafCallbacks: FrameRequestCallback[]
    let rafIdCounter: number

    beforeEach(() => {
      rafCallbacks = []
      rafIdCounter = 0

      vi.spyOn(window, 'requestAnimationFrame').mockImplementation((cb) => {
        rafCallbacks.push(cb)
        return ++rafIdCounter
      })
      vi.spyOn(window, 'cancelAnimationFrame').mockImplementation(() => {})
    })

    afterEach(() => {
      vi.restoreAllMocks()
    })

    function flushFrames(count: number) {
      for (let i = 0; i < count; i++) {
        const cb = rafCallbacks.shift()
        if (cb) cb(performance.now())
      }
    }

    it('sets isAnimating to true when animation starts', () => {
      const bars = siegmeister.renderBars(sampleBars, 1, container)

      siegmeister.animateBars(sampleBars, bars, 1)

      expect(siegmeister.isAnimating.value).toBe(true)
    })

    it('sets isAnimating to false after 240 frames', () => {
      const bars = siegmeister.renderBars(sampleBars, 1, container)

      siegmeister.animateBars(sampleBars, bars, 1)

      // Run all 240 frames
      flushFrames(240)

      expect(siegmeister.isAnimating.value).toBe(false)
    })

    it('calls onComplete after animation finishes', () => {
      const bars = siegmeister.renderBars(sampleBars, 1, container)

      siegmeister.animateBars(sampleBars, bars, 1)
      flushFrames(240)

      expect(onComplete).toHaveBeenCalledTimes(1)
    })

    it('updates bar widths during animation', () => {
      const bars = siegmeister.renderBars(sampleBars, 1, container)

      siegmeister.animateBars(sampleBars, bars, 1)

      // Run enough frames for widths to be non-zero (halfway through animation)
      flushFrames(120)

      // After 120 frames (halfway), the bar with highest x2 should have non-zero width
      const width = parseFloat(bars[1].style.width)
      expect(width).toBeGreaterThan(0)
    })

    it('applies blink styling to top 3 bars after animation', () => {
      // Bar x2 values: 0.5, 0.8, 0.3
      // Sorted descending: 0.8 (index 1), 0.5 (index 0), 0.3 (index 2)
      // Top 3 unique x2 values => all 3 bars get blink
      const bars = siegmeister.renderBars(sampleBars, 1, container)

      siegmeister.animateBars(sampleBars, bars, 1)
      flushFrames(240)

      expect(bars[0].classList.contains('blink')).toBe(true)
      expect(bars[1].classList.contains('blink')).toBe(true)
      expect(bars[2].classList.contains('blink')).toBe(true)
    })

    it('applies blink color to top bars after animation', () => {
      const bars = siegmeister.renderBars(sampleBars, 1, container)

      siegmeister.animateBars(sampleBars, bars, 1)
      flushFrames(240)

      // The bar with highest x2 (0.8, index 1) should have blink color
      expect(bars[1].style.backgroundColor).toBe('#ff0000')
    })

    it('only blinks top 3 unique x2 values when more than 3 bars exist', () => {
      const manyBars: SiegmeisterBarData[] = [
        { x1: 0.1, y1: 0.1, x2: 0.9, y2: 0.2 }, // 1st place
        { x1: 0.1, y1: 0.3, x2: 0.7, y2: 0.4 }, // 2nd place
        { x1: 0.1, y1: 0.5, x2: 0.5, y2: 0.6 }, // 3rd place
        { x1: 0.1, y1: 0.7, x2: 0.2, y2: 0.8 }, // 4th place - no blink
      ]

      const bars = siegmeister.renderBars(manyBars, 1, container)
      siegmeister.animateBars(manyBars, bars, 1)
      flushFrames(240)

      expect(bars[0].classList.contains('blink')).toBe(true)
      expect(bars[1].classList.contains('blink')).toBe(true)
      expect(bars[2].classList.contains('blink')).toBe(true)
      expect(bars[3].classList.contains('blink')).toBe(false)
    })

    it('blinks bars with tied x2 values within top 3', () => {
      const tiedBars: SiegmeisterBarData[] = [
        { x1: 0.1, y1: 0.1, x2: 0.9, y2: 0.2 }, // 1st
        { x1: 0.1, y1: 0.3, x2: 0.9, y2: 0.4 }, // 1st (tied)
        { x1: 0.1, y1: 0.5, x2: 0.5, y2: 0.6 }, // 2nd
        { x1: 0.1, y1: 0.7, x2: 0.3, y2: 0.8 }, // 3rd
      ]

      const bars = siegmeister.renderBars(tiedBars, 1, container)
      siegmeister.animateBars(tiedBars, bars, 1)
      flushFrames(240)

      // All 4 should blink: 2 tied for 1st + 2nd + 3rd = 4 bars, 3 unique values
      expect(bars[0].classList.contains('blink')).toBe(true)
      expect(bars[1].classList.contains('blink')).toBe(true)
      expect(bars[2].classList.contains('blink')).toBe(true)
      expect(bars[3].classList.contains('blink')).toBe(true)
    })
  })
})
