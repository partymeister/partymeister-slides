import { ref, computed } from 'vue'
import type { SlideElement, ElementOrder } from '@/types/editor'
import type { useEditorStore } from '@/stores/editorStore'

interface HistorySnapshot {
  elements: Record<string, SlideElement>
  elementOrder: ElementOrder[]
}

export function useHistory(editorStore: ReturnType<typeof useEditorStore>) {
  const undoStack = ref<string[]>([])
  const redoStack = ref<string[]>([])
  const maxHistory = 50

  function currentSnapshot(): string {
    const snapshot: HistorySnapshot = {
      elements: JSON.parse(JSON.stringify(editorStore.elements)),
      elementOrder: JSON.parse(JSON.stringify(editorStore.elementOrder)),
    }
    return JSON.stringify(snapshot)
  }

  function restoreSnapshot(json: string): void {
    const snapshot: HistorySnapshot = JSON.parse(json)
    editorStore.elements = snapshot.elements
    editorStore.elementOrder = snapshot.elementOrder
    editorStore.isDirty = true
  }

  function checkpoint(): void {
    const snap = currentSnapshot()
    undoStack.value.push(snap)
    if (undoStack.value.length > maxHistory) {
      undoStack.value.splice(0, undoStack.value.length - maxHistory)
    }
    redoStack.value = []
  }

  function undo(): void {
    if (undoStack.value.length === 0) return
    const previous = undoStack.value.pop()!
    redoStack.value.push(currentSnapshot())
    restoreSnapshot(previous)
  }

  function redo(): void {
    if (redoStack.value.length === 0) return
    const next = redoStack.value.pop()!
    undoStack.value.push(currentSnapshot())
    restoreSnapshot(next)
  }

  const canUndo = computed(() => undoStack.value.length > 0)
  const canRedo = computed(() => redoStack.value.length > 0)

  return { checkpoint, undo, redo, canUndo, canRedo }
}
