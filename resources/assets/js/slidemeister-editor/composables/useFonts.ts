import { ref } from 'vue'
import { useApi } from '@/composables/useApi'

const SYSTEM_FONTS = [
  'Arial', 'Verdana', 'Georgia', 'Times New Roman',
  'Courier New', 'Helvetica', 'Impact', 'Comic Sans MS',
]

export function useFonts() {
  const availableFonts = ref<string[]>([...SYSTEM_FONTS])
  const loadedFonts = new Set<string>(SYSTEM_FONTS)

  async function fetchFonts(): Promise<void> {
    try {
      const api = useApi()
      const fonts = await api.listFonts()
      for (const f of fonts) {
        if (!availableFonts.value.includes(f.family)) {
          availableFonts.value.push(f.family)
        }
      }
    } catch {
      // API may not have a fonts endpoint — that's OK
    }
  }

  function loadFont(family: string): void {
    if (loadedFonts.has(family)) return
    loadedFonts.add(family)
    // Inject Google Font link
    const link = document.createElement('link')
    link.rel = 'stylesheet'
    link.href = `https://fonts.googleapis.com/css2?family=${encodeURIComponent(family)}&display=swap`
    document.head.appendChild(link)
  }

  return { availableFonts, fetchFonts, loadFont }
}
