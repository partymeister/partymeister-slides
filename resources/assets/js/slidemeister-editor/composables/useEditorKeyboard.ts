import { onMounted, onUnmounted } from 'vue'

interface KeyboardOptions {
  undo: () => void
  redo: () => void
  save: () => void
  cloneElement: () => void
  deselect: () => void
  nudge: (dx: number, dy: number) => void
  isTextEditing: () => boolean
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
    } else if (e.key === 'ArrowUp' || e.key === 'ArrowDown' || e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
      e.preventDefault()
      const step = e.shiftKey ? 10 : 1
      const dx = e.key === 'ArrowLeft' ? -step : e.key === 'ArrowRight' ? step : 0
      const dy = e.key === 'ArrowUp' ? -step : e.key === 'ArrowDown' ? step : 0
      opts.nudge(dx, dy)
    }
  }

  onMounted(() => window.addEventListener('keydown', onKeyDown))
  onUnmounted(() => window.removeEventListener('keydown', onKeyDown))
}
