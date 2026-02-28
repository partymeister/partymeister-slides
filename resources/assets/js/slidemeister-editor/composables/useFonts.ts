import { ref } from 'vue'
import { useApi } from '@/composables/useApi'

const SYSTEM_FONTS = ['Arial', 'Verdana']

export function useFonts() {
  const availableFonts = ref<string[]>([...SYSTEM_FONTS])
  const loadedPaths = new Set<string>()

  async function fetchFonts(): Promise<void> {
    try {
      const api = useApi()
      const fonts = await api.listFonts()
      for (const f of fonts) {
        // Add to dropdown list
        if (!availableFonts.value.includes(f.family)) {
          availableFonts.value.push(f.family)
        }
        // Inject CSS stylesheet for the font (dedupe by path)
        if (f.path && !loadedPaths.has(f.path)) {
          loadedPaths.add(f.path)
          const link = document.createElement('link')
          link.rel = 'stylesheet'
          link.href = f.path
          document.head.appendChild(link)
        }
      }
    } catch {
      // API may not have a fonts endpoint — that's OK
    }
  }

  return { availableFonts, fetchFonts }
}
