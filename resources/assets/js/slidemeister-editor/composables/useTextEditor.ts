import { ref, computed } from 'vue'
import { Editor } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import TextAlign from '@tiptap/extension-text-align'
import { TextStyle } from '@tiptap/extension-text-style'
import Color from '@tiptap/extension-color'
import type { useEditorStore } from '@/stores/editorStore'

export function useTextEditor(
  editorStore: ReturnType<typeof useEditorStore>,
  checkpoint: () => void,
  onContentUpdate?: (elementName: string) => void,
) {
  const editingElementName = ref<string | null>(null)
  const editor = ref<Editor | null>(null)

  const isEditing = computed(() => editingElementName.value !== null)

  function startEditing(elementName: string): void {
    // Don't start if element isn't editable
    const el = editorStore.elements[elementName]
    if (!el || !el.properties.editable) return

    // Stop any current editing first
    stopEditing()

    checkpoint()
    editingElementName.value = elementName

    // Strip any auto-linked <a> tags from previous edits
    const cleanContent = (el.properties.content || '').replace(/<a[^>]*>(.*?)<\/a>/gi, '$1')

    editor.value = new Editor({
      content: cleanContent,
      extensions: [
        StarterKit.configure({
          link: false,  // Disable auto-linking of URLs
        }),
        TextAlign.configure({ types: ['heading', 'paragraph'] }),
        TextStyle,
        Color,
      ],
      onUpdate: () => {
        if (editingElementName.value) {
          // Sync content back to store on every keystroke
          const html = editor.value?.getHTML() ?? ''
          editorStore.updateElementProperty(
            editingElementName.value,
            'properties.content',
            html,
          )
          onContentUpdate?.(editingElementName.value)
        }
      },
    })
  }

  function stopEditing(): void {
    if (!editor.value || !editingElementName.value) return

    // Save content back to store — insert <br> in empty paragraphs so they
    // keep their line height when rendered via v-html outside the editor
    const html = editor.value.getHTML().replace(/<p><\/p>/g, '<p><br></p>')
    editorStore.updateElementProperty(
      editingElementName.value,
      'properties.content',
      html,
    )

    editor.value.destroy()
    editor.value = null
    editingElementName.value = null
  }

  function destroy(): void {
    if (editor.value) {
      editor.value.destroy()
      editor.value = null
    }
    editingElementName.value = null
  }

  return {
    editingElementName,
    editor,
    isEditing,
    startEditing,
    stopEditing,
    destroy,
  }
}
