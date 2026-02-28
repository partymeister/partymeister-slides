import { ref, computed } from 'vue'
import type { SlideElement, ElementOrder } from '@common/types/editor'
import type { useEditorStore } from '@/stores/editorStore'

interface HistoryEntry {
  snapshot: string
  description: string
}

export function useHistory(editorStore: ReturnType<typeof useEditorStore>) {
  const undoStack = ref<HistoryEntry[]>([])
  const redoStack = ref<HistoryEntry[]>([])
  const maxHistory = 50
  const lastAction = ref('')

  function currentSnapshot(): string {
    return JSON.stringify({
      elements: JSON.parse(JSON.stringify(editorStore.elements)),
      elementOrder: JSON.parse(JSON.stringify(editorStore.elementOrder)),
    })
  }

  function restoreSnapshot(json: string): void {
    const snapshot = JSON.parse(json)
    editorStore.elements = snapshot.elements
    editorStore.elementOrder = snapshot.elementOrder
    editorStore.isDirty = true
  }

  function checkpoint(description = 'Change'): void {
    const snap = currentSnapshot()
    undoStack.value.push({ snapshot: snap, description })
    if (undoStack.value.length > maxHistory) {
      undoStack.value.splice(0, undoStack.value.length - maxHistory)
    }
    redoStack.value = []
    lastAction.value = ''
  }

  function undo(): void {
    if (undoStack.value.length === 0) return
    const entry = undoStack.value.pop()!
    redoStack.value.push({ snapshot: currentSnapshot(), description: entry.description })
    restoreSnapshot(entry.snapshot)
    lastAction.value = `Undo: ${entry.description}`
  }

  function redo(): void {
    if (redoStack.value.length === 0) return
    const entry = redoStack.value.pop()!
    undoStack.value.push({ snapshot: currentSnapshot(), description: entry.description })
    restoreSnapshot(entry.snapshot)
    lastAction.value = `Redo: ${entry.description}`
  }

  function reset(): void {
    undoStack.value = []
    redoStack.value = []
    lastAction.value = ''
  }

  const canUndo = computed(() => undoStack.value.length > 0)
  const canRedo = computed(() => redoStack.value.length > 0)
  const undoDescription = computed(() =>
    undoStack.value.length > 0 ? undoStack.value[undoStack.value.length - 1].description : '',
  )
  const redoDescription = computed(() =>
    redoStack.value.length > 0 ? redoStack.value[redoStack.value.length - 1].description : '',
  )

  return {
    checkpoint, undo, redo, reset,
    canUndo, canRedo,
    undoDescription, redoDescription,
    lastAction,
  }
}
