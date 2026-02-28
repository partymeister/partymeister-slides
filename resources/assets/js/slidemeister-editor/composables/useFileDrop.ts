import { ref } from 'vue'
import type { useEditorStore } from '@/stores/editorStore'

export function useFileDrop(
  editorStore: ReturnType<typeof useEditorStore>,
  checkpoint: () => void,
) {
  const isDragging = ref(false)
  let dragCounter = 0 // Track nested drag enter/leave

  function onDragEnter(e: DragEvent): void {
    e.preventDefault()
    dragCounter++
    if (e.dataTransfer?.types.includes('Files')) {
      isDragging.value = true
    }
  }

  function onDragOver(e: DragEvent): void {
    e.preventDefault()
  }

  function onDragLeave(e: DragEvent): void {
    e.preventDefault()
    dragCounter--
    if (dragCounter <= 0) {
      isDragging.value = false
      dragCounter = 0
    }
  }

  function onDrop(e: DragEvent): void {
    e.preventDefault()
    isDragging.value = false
    dragCounter = 0

    const files = e.dataTransfer?.files
    if (!files || files.length === 0) return

    for (const file of Array.from(files)) {
      if (!file.type.startsWith('image/')) continue
      const reader = new FileReader()
      reader.onload = () => {
        const dataUrl = reader.result as string
        // Create an Image to get dimensions
        const img = new Image()
        img.onload = () => {
          const targetWidth = 200
          const ratio = img.height / img.width
          const targetHeight = Math.round(targetWidth * ratio)

          checkpoint()
          const name = editorStore.addElement(null, dataUrl)
          editorStore.updateElementProperty(name, 'properties.coordinates.width', targetWidth)
          editorStore.updateElementProperty(name, 'properties.coordinates.height', targetHeight)
          editorStore.updateElementProperty(name, 'properties.content', '')
        }
        img.src = dataUrl
      }
      reader.readAsDataURL(file)
    }
  }

  return { isDragging, onDragEnter, onDragOver, onDragLeave, onDrop }
}
