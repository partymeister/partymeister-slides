import { mount } from '@vue/test-utils'
import { defineComponent } from 'vue'
import { useWindowResize } from '@/composables/useWindowResize'

describe('useWindowResize', () => {
  const addEventListenerSpy = vi.spyOn(window, 'addEventListener')
  const removeEventListenerSpy = vi.spyOn(window, 'removeEventListener')

  beforeEach(() => {
    vi.clearAllMocks()
    // Set window dimensions
    Object.defineProperty(window, 'innerWidth', { value: 1920, writable: true })
    Object.defineProperty(window, 'innerHeight', { value: 1080, writable: true })
  })

  function mountWithComposable(updateZoom: (w: number, h: number) => void) {
    return mount(
      defineComponent({
        setup() {
          return useWindowResize(updateZoom)
        },
        template: '<div />',
      }),
    )
  }

  it('calls updateZoom with window dimensions on mount', () => {
    const updateZoom = vi.fn()

    mountWithComposable(updateZoom)

    expect(updateZoom).toHaveBeenCalledWith(1920, 1080)
  })

  it('registers resize event listener on mount', () => {
    const updateZoom = vi.fn()

    mountWithComposable(updateZoom)

    expect(addEventListenerSpy).toHaveBeenCalledWith('resize', expect.any(Function))
  })

  it('removes resize event listener on unmount', () => {
    const updateZoom = vi.fn()

    const wrapper = mountWithComposable(updateZoom)
    wrapper.unmount()

    expect(removeEventListenerSpy).toHaveBeenCalledWith('resize', expect.any(Function))
  })

  it('handleResize can be called manually', () => {
    const updateZoom = vi.fn()

    const wrapper = mountWithComposable(updateZoom)
    updateZoom.mockClear()

    // Change window dimensions
    Object.defineProperty(window, 'innerWidth', { value: 800 })
    Object.defineProperty(window, 'innerHeight', { value: 600 })

    // Call handleResize manually via the returned value
    const vm = wrapper.vm as unknown as { handleResize: () => void }
    vm.handleResize()

    expect(updateZoom).toHaveBeenCalledWith(800, 600)
  })
})
