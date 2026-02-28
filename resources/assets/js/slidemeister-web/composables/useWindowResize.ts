import { onMounted, onUnmounted } from 'vue'

const BASE_WIDTH = 960
const BASE_HEIGHT = 540

export function useWindowResize(updateZoom: (width: number, height: number) => void) {
  function handleResize() {
    updateZoom(window.innerWidth, window.innerHeight)
  }

  onMounted(() => {
    handleResize()
    window.addEventListener('resize', handleResize)
  })

  onUnmounted(() => {
    window.removeEventListener('resize', handleResize)
  })

  return { handleResize }
}
