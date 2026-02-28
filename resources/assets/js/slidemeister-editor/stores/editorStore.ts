import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { SlideElement, SlideDefinitions, TemplateType, ElementOrder } from '@common/types/editor'
import { createDefaultElement } from '@common/types/editor'
import { useApi } from '@common/composables/useApi'

export const useEditorStore = defineStore('editor', () => {
  const api = useApi()

  // ── Identity ──
  const templateId = ref<number | null>(null)
  const templateName = ref('')
  const templateType = ref<TemplateType>('basic')

  // ── Elements ──
  const elements = ref<Record<string, SlideElement>>({})
  const elementOrder = ref<ElementOrder[]>([])
  const activeElementName = ref<string | null>(null)
  const hoverElementName = ref<string | null>(null)

  // ── Dirty tracking ──
  const isDirty = ref(false)
  const isSaving = ref(false)

  // ── Computed ──
  const activeElement = computed<SlideElement | null>(() => {
    if (activeElementName.value === null) return null
    return elements.value[activeElementName.value] ?? null
  })

  const sortedElements = computed<SlideElement[]>(() => {
    return elementOrder.value
      .map((entry) => elements.value[entry.name])
      .filter((el): el is SlideElement => el !== undefined)
  })

  // ── Element CRUD ──

  function addElement(image?: string | null, dataUrl?: string | null): string {
    const name = `element_${Math.floor(Math.random() * 100000000)}`
    const el = createDefaultElement(name)
    if (image != null) {
      el.properties.image = image
    }
    if (dataUrl != null) {
      el.properties.dataUrl = dataUrl
    }
    el.properties.zIndex = getNextZIndex()
    elements.value[name] = el
    elementOrder.value.unshift({ name, zIndex: el.properties.zIndex })
    activeElementName.value = name
    isDirty.value = true
    return name
  }

  function cloneElement(name: string): string {
    const source = elements.value[name]
    if (!source) throw new Error(`Element "${name}" not found`)

    const newName = `element_${Math.floor(Math.random() * 100000000)}`
    const cloned: SlideElement = JSON.parse(JSON.stringify(source))
    cloned.name = newName
    cloned.properties.zIndex = getNextZIndex()

    elements.value[newName] = cloned
    elementOrder.value.unshift({ name: newName, zIndex: cloned.properties.zIndex })
    activeElementName.value = newName
    isDirty.value = true
    return newName
  }

  function deleteElement(name: string): void {
    delete elements.value[name]
    elementOrder.value = elementOrder.value.filter((e) => e.name !== name)
    if (activeElementName.value === name) {
      activeElementName.value = null
    }
    isDirty.value = true
  }

  function updateElementProperty(name: string, path: string, value: unknown): void {
    const el = elements.value[name]
    if (!el) return

    const parts = path.split('.')
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    let target: any = el
    for (let i = 0; i < parts.length - 1; i++) {
      target = target[parts[i]]
      if (target === undefined || target === null) return
    }
    target[parts[parts.length - 1]] = value
    isDirty.value = true
  }

  // ── Layer ordering ──

  function reorderElements(newOrder: ElementOrder[]): void {
    let z = 2000
    for (const entry of newOrder) {
      entry.zIndex = z
      const el = elements.value[entry.name]
      if (el) {
        el.properties.zIndex = z
      }
      z--
    }
    elementOrder.value = newOrder
    isDirty.value = true
  }

  function getNextZIndex(): number {
    return 2000 + elementOrder.value.length
  }

  // ── Selection ──

  function setActiveElement(name: string | null): void {
    activeElementName.value = name
  }

  function setHoverElement(name: string | null): void {
    hoverElementName.value = name
  }

  // ── Serialization ──

  function toDefinitions(): SlideDefinitions {
    return {
      id: templateName.value,
      type: templateType.value,
      elements: JSON.parse(JSON.stringify(elements.value)),
    }
  }

  function loadDefinitions(defs: SlideDefinitions): void {
    templateName.value = defs.id
    templateType.value = (defs.type as TemplateType) || 'basic'
    elements.value = defs.elements

    // Rebuild elementOrder from elements, sorted by zIndex descending
    const order: ElementOrder[] = Object.values(defs.elements).map((el) => ({
      name: el.name,
      zIndex: el.properties.zIndex,
    }))
    order.sort((a, b) => b.zIndex - a.zIndex)
    elementOrder.value = order

    activeElementName.value = null
    hoverElementName.value = null
    isDirty.value = false
  }

  // ── Persistence ──

  async function loadFromApi(id: number): Promise<void> {
    const response = await api.getTemplate(id)
    const defs: SlideDefinitions = JSON.parse(response.definitions)
    loadDefinitions(defs)
    templateId.value = id
    templateName.value = response.name
  }

  async function saveToApi(): Promise<void> {
    isSaving.value = true
    try {
      const defs = toDefinitions()
      const payload = {
        name: templateName.value,
        template_for: templateType.value,
        definitions: JSON.stringify(defs),
        cached_html_preview: '',
        cached_html_final: '',
      }

      if (templateId.value !== null) {
        await api.saveTemplate(templateId.value, payload)
      } else {
        const response = await api.createTemplate(payload)
        templateId.value = response.id
      }

      isDirty.value = false
    } finally {
      isSaving.value = false
    }
  }

  return {
    // Identity
    templateId,
    templateName,
    templateType,

    // Elements
    elements,
    elementOrder,
    activeElementName,
    hoverElementName,

    // Dirty tracking
    isDirty,
    isSaving,

    // Computed
    activeElement,
    sortedElements,

    // Element CRUD
    addElement,
    cloneElement,
    deleteElement,
    updateElementProperty,

    // Layer ordering
    reorderElements,
    getNextZIndex,

    // Selection
    setActiveElement,
    setHoverElement,

    // Serialization
    toDefinitions,
    loadDefinitions,

    // Persistence
    loadFromApi,
    saveToApi,
  }
})
