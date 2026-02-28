import type { useEditorStore } from '@/stores/editorStore'
import type { SlideDefinitions } from '@common/types/editor'

export function useFileIO(editorStore: ReturnType<typeof useEditorStore>) {
  function exportJson(): void {
    const defs = editorStore.toDefinitions()
    const json = JSON.stringify(defs, null, 2)
    const blob = new Blob([json], { type: 'application/json' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `${defs.id || 'template'}.json`
    a.click()
    URL.revokeObjectURL(url)
  }

  function importJson(): void {
    const input = document.createElement('input')
    input.type = 'file'
    input.accept = '.json'
    input.onchange = () => {
      const file = input.files?.[0]
      if (!file) return
      const reader = new FileReader()
      reader.onload = () => {
        try {
          const defs: SlideDefinitions = JSON.parse(reader.result as string)
          if (!defs.elements || typeof defs.elements !== 'object') {
            throw new Error('Invalid template format')
          }
          editorStore.loadDefinitions(defs)
        } catch (err) {
          console.error('Failed to import template:', err)
        }
      }
      reader.readAsText(file)
    }
    input.click()
  }

  return { exportJson, importJson }
}
