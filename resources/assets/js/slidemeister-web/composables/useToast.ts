import { ref } from 'vue'

export interface Toast {
  id: number
  message: string
  type: 'success' | 'error' | 'info'
  timestamp: number
}

let toastId = 0

export function useToast() {
  const toasts = ref<Toast[]>([])

  function addToast(message: string, type: Toast['type'] = 'info', duration = 3000) {
    const toast: Toast = { id: ++toastId, message, type, timestamp: Date.now() }
    toasts.value.push(toast)
    if (duration > 0) {
      setTimeout(() => removeToast(toast.id), duration)
    }
  }

  function removeToast(id: number) {
    toasts.value = toasts.value.filter((t) => t.id !== id)
  }

  function success(message: string) { addToast(message, 'success') }
  function error(message: string) { addToast(message, 'error', 5000) }
  function info(message: string) { addToast(message, 'info') }

  return { toasts, success, error, info, removeToast }
}
