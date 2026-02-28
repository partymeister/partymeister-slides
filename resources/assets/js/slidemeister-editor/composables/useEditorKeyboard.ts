import { onMounted, onUnmounted } from 'vue'

interface KeyboardOptions {
  undo: () => void
  redo: () => void
  save: () => void
  deleteElement: () => void
  cloneElement: () => void
  deselect: () => void
  isTextEditing: () => boolean  // Return true to suppress most shortcuts
}

export function useEditorKeyboard(opts: KeyboardOptions) {
  function onKeyDown(e: KeyboardEvent) {
    const mod = e.metaKey || e.ctrlKey

    // Escape always works
    if (e.key === 'Escape') {
      e.preventDefault()
      opts.deselect()
      return
    }

    // Block shortcuts during text editing (except Escape above)
    if (opts.isTextEditing()) return

    if (mod && e.shiftKey && e.key === 'z') {
      e.preventDefault()
      opts.redo()
    } else if (mod && e.key === 'z') {
      e.preventDefault()
      opts.undo()
    } else if (mod && e.key === 's') {
      e.preventDefault()
      opts.save()
    } else if (mod && e.key === 'd') {
      e.preventDefault()
      opts.cloneElement()
    } else if (e.key === 'Delete' || e.key === 'Backspace') {
      e.preventDefault()
      opts.deleteElement()
    }
  }

  onMounted(() => window.addEventListener('keydown', onKeyDown))
  onUnmounted(() => window.removeEventListener('keydown', onKeyDown))
}
