<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApi } from '@common/composables/useApi'
import { serializeElements } from '@common/composables/useHtmlSerializer'
import type { SlideElement } from '@common/types/editor'
import type { SlideTemplateResponse, SlideResponse } from '@common/types/api'

const api = useApi()

// Map template_for values to valid slide_type values
const templateForToSlideType: Record<string, string> = {
  coming_up: 'comingup',
  now: 'now',
  end: 'end',
  basic: 'default',
  competition: 'compo',
  competition_entry_1: 'compo',
  timetable: 'timetable',
  participants: 'default',
  prizegiving: 'default',
  comments: 'default',
  end_of_pg: 'end',
}

// Categories for slide creation
const categories = ref<{ id: number; name: string }[]>([])

// Cache rendered HTML from definitions
const previewCache = new Map<string, string>()

function getPreviewHtml(item: { definitions: string; cached_html_preview: string | null }, key: string): string {
  if (item.cached_html_preview) return item.cached_html_preview
  if (previewCache.has(key)) return previewCache.get(key)!
  try {
    const defs = JSON.parse(item.definitions)
    const elements: Record<string, SlideElement> = defs.elements || {}
    const html = serializeElements(elements)
    previewCache.set(key, html)
    return html
  } catch {
    return ''
  }
}

const initialTab = window.location.hash === '#slides' ? 'slides' : 'templates'
const activeTab = ref<'templates' | 'slides'>(initialTab)
const loading = ref(true)
const error = ref('')

// Templates tab
const templates = ref<SlideTemplateResponse[]>([])
const templatePage = ref(1)
const templateLastPage = ref(1)

// Slides tab
const slides = ref<SlideResponse[]>([])
const slidePage = ref(1)
const slideLastPage = ref(1)
const slideSearch = ref('')

function flattenTree(nodes: any[], depth = 0): { id: number; name: string }[] {
  const result: { id: number; name: string }[] = []
  for (const node of nodes) {
    result.push({ id: node.id, name: '\u00A0'.repeat(depth * 2) + node.name })
    if (node.children?.length) {
      result.push(...flattenTree(node.children, depth + 1))
    }
  }
  return result
}

onMounted(async () => {
  if (initialTab === 'slides') loadSlides()
  else loadTemplates()
  try {
    const res = await api.request<{ data: any[] }>('GET', '/api/category_trees?scope=slides')
    const trees = res.data || []
    const slidesTree = trees.find((t: any) => /^slides$/i.test(t.name))
    categories.value = slidesTree?.children
      ? flattenTree(slidesTree.children)
      : flattenTree(trees)
  } catch { /* categories will be empty */ }
})

async function loadTemplates(page = 1) {
  loading.value = true
  error.value = ''
  try {
    const res = await api.listTemplates(`page=${page}&per_page=50`)
    templates.value = res.data
    templatePage.value = res.meta.current_page
    templateLastPage.value = res.meta.last_page
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Failed to load templates'
  } finally {
    loading.value = false
  }
}

async function loadSlides(page = 1) {
  loading.value = true
  error.value = ''
  try {
    const params = [`page=${page}`, 'per_page=50']
    if (slideSearch.value) params.push(`filter[search]=${encodeURIComponent(slideSearch.value)}`)
    const res = await api.listSlides(params.join('&'))
    slides.value = res.data
    slidePage.value = res.meta.current_page
    slideLastPage.value = res.meta.last_page
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Failed to load slides'
  } finally {
    loading.value = false
  }
}

function switchTab(tab: 'templates' | 'slides') {
  activeTab.value = tab
  if (tab === 'templates' && templates.value.length === 0) loadTemplates()
  if (tab === 'slides' && slides.value.length === 0) loadSlides()
}

function editTemplate(id: number) {
  window.location.href = `/slidemeister-editor/template/${id}`
}

function editSlide(id: number) {
  window.location.href = `/slidemeister-editor/slide/${id}`
}

// Create slide modal state
const showCreateModal = ref(false)
const createModalTemplate = ref<SlideTemplateResponse | null>(null)
const createSlideName = ref('')
const createCategoryId = ref<number | null>(null)

function openCreateModal(template: SlideTemplateResponse) {
  createModalTemplate.value = template
  createSlideName.value = `New slide from ${template.name}`
  createCategoryId.value = categories.value.length > 0 ? categories.value[0].id : null
  showCreateModal.value = true
}

async function confirmCreateSlide() {
  const template = createModalTemplate.value
  if (!template || !createCategoryId.value) return
  showCreateModal.value = false
  try {
    const slideType = templateForToSlideType[template.template_for] || 'default'
    const previewHtml = getPreviewHtml(template, `tpl_${template.id}`)
    const slide = await api.createSlide({
      name: createSlideName.value,
      slide_type: slideType,
      category_id: createCategoryId.value,
      slide_template_id: template.id,
      definitions: template.definitions,
      cached_html_preview: previewHtml,
      cached_html_final: previewHtml,
    })
    window.location.href = `/slidemeister-editor/slide/${slide.id}`
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Failed to create slide'
  }
}

async function deleteTemplate(template: SlideTemplateResponse) {
  if (!confirm(`Delete template "${template.name}"? This cannot be undone.`)) return
  try {
    await api.deleteTemplate(template.id)
    templates.value = templates.value.filter(t => t.id !== template.id)
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Failed to delete template'
  }
}

let searchTimeout: ReturnType<typeof setTimeout>
function onSearchInput() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => loadSlides(1), 300)
}
</script>

<template>
  <div class="start-page">
    <div class="start-toolbar">
      <span class="start-title">Slidemeister Editor</span>
    </div>

    <div class="tab-bar">
      <button :class="{ active: activeTab === 'templates' }" @click="switchTab('templates')">Templates</button>
      <button :class="{ active: activeTab === 'slides' }" @click="switchTab('slides')">Slides</button>
    </div>

    <div v-if="activeTab === 'slides'" class="filter-bar">
      <input
        v-model="slideSearch"
        type="text"
        placeholder="Search slides..."
        class="search-input"
        @input="onSearchInput"
      />
    </div>

    <div v-if="loading" class="status-msg">Loading...</div>
    <div v-else-if="error" class="status-msg error">{{ error }}</div>

    <div v-else class="card-grid">
      <!-- Templates -->
      <template v-if="activeTab === 'templates'">
        <div v-for="t in templates" :key="t.id" class="card">
          <div class="card-preview">
            <div class="preview-container" v-html="getPreviewHtml(t, `tpl_${t.id}`)" />
          </div>
          <div class="card-info">
            <div class="card-name">{{ t.name }}</div>
            <div class="card-meta">{{ t.template_for }}</div>
          </div>
          <div class="card-actions">
            <button class="action-btn" @click="editTemplate(t.id)">Edit</button>
            <button class="action-btn primary" @click="openCreateModal(t)">Create Slide</button>
            <button class="action-btn danger" @click="deleteTemplate(t)">Delete</button>
          </div>
        </div>
      </template>

      <!-- Slides -->
      <template v-if="activeTab === 'slides'">
        <div v-for="s in slides" :key="s.id" class="card">
          <div class="card-preview">
            <div class="preview-container" v-html="getPreviewHtml(s, `slide_${s.id}`)" />
          </div>
          <div class="card-info">
            <div class="card-name">{{ s.name }}</div>
            <div class="card-meta">{{ s.slide_type || 'slide' }}</div>
          </div>
          <div class="card-actions">
            <button class="action-btn" @click="editSlide(s.id)">Edit</button>
          </div>
        </div>
      </template>
    </div>

    <!-- Create Slide Modal -->
    <div v-if="showCreateModal" class="modal-overlay" @click.self="showCreateModal = false">
      <div class="modal">
        <h3>Create Slide</h3>
        <label class="modal-label">
          Name
          <input v-model="createSlideName" type="text" class="modal-input" />
        </label>
        <label class="modal-label">
          Category
          <select v-model="createCategoryId" class="modal-input">
            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </label>
        <div class="modal-actions">
          <button class="action-btn" @click="showCreateModal = false">Cancel</button>
          <button class="action-btn primary" :disabled="!createCategoryId" @click="confirmCreateSlide">Create</button>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="!loading && !error" class="pagination">
      <template v-if="activeTab === 'templates' && templateLastPage > 1">
        <button :disabled="templatePage <= 1" @click="loadTemplates(templatePage - 1)">Prev</button>
        <span>{{ templatePage }} / {{ templateLastPage }}</span>
        <button :disabled="templatePage >= templateLastPage" @click="loadTemplates(templatePage + 1)">Next</button>
      </template>
      <template v-if="activeTab === 'slides' && slideLastPage > 1">
        <button :disabled="slidePage <= 1" @click="loadSlides(slidePage - 1)">Prev</button>
        <span>{{ slidePage }} / {{ slideLastPage }}</span>
        <button :disabled="slidePage >= slideLastPage" @click="loadSlides(slidePage + 1)">Next</button>
      </template>
    </div>
  </div>
</template>

<style scoped>
.start-page {
  display: flex;
  flex-direction: column;
  height: 100vh;
  overflow: hidden;
}

.start-toolbar {
  display: flex;
  align-items: center;
  padding: 8px 12px;
  background: #1e1e1e;
  border-bottom: 1px solid #333;
  flex-shrink: 0;
}

.start-title {
  font-weight: 600;
  font-size: 13px;
  color: #eee;
}

.tab-bar {
  display: flex;
  background: #1e1e1e;
  border-bottom: 1px solid #333;
  flex-shrink: 0;
}

.tab-bar button {
  flex: 0;
  padding: 8px 20px;
  background: none;
  border: none;
  border-bottom: 2px solid transparent;
  color: #888;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  cursor: pointer;
  transition: color 0.15s, border-color 0.15s;
}

.tab-bar button:hover {
  color: #ccc;
}

.tab-bar button.active {
  color: #4a9eff;
  border-bottom-color: #4a9eff;
}

.filter-bar {
  padding: 8px 12px;
  background: #1a1a1a;
  border-bottom: 1px solid #333;
  flex-shrink: 0;
}

.search-input {
  width: 300px;
  background: #222;
  border: 1px solid #444;
  color: #eee;
  padding: 6px 10px;
  border-radius: 4px;
  font-size: 13px;
}

.search-input::placeholder {
  color: #666;
}

.status-msg {
  display: flex;
  align-items: center;
  justify-content: center;
  flex: 1;
  font-size: 14px;
  color: #888;
}

.status-msg.error {
  color: #ff6b6b;
}

.card-grid {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  align-content: flex-start;
}

.card {
  width: 260px;
  background: #222;
  border: 1px solid #333;
  border-radius: 4px;
  overflow: hidden;
  transition: border-color 0.15s;
}

.card:hover {
  border-color: #555;
}

.card-preview {
  width: 260px;
  height: 146px;
  overflow: hidden;
  background: #000;
}

.preview-container {
  width: 960px;
  height: 540px;
  position: relative;
  transform: scale(0.2708);
  transform-origin: top left;
}

.card-info {
  padding: 8px 10px;
}

.card-name {
  font-size: 12px;
  font-weight: 600;
  color: #eee;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.card-meta {
  font-size: 11px;
  color: #888;
  margin-top: 2px;
}

.card-actions {
  display: flex;
  gap: 4px;
  padding: 6px 10px 8px;
}

.action-btn {
  background: #2a2a2a;
  border: 1px solid #444;
  color: #eee;
  padding: 3px 10px;
  border-radius: 3px;
  cursor: pointer;
  font-size: 12px;
}

.action-btn:hover {
  background: #3a3a3a;
}

.action-btn.primary {
  background: #1a5a2a;
  border-color: #2a7a3a;
}

.action-btn.primary:hover {
  background: #2a7a3a;
}

.action-btn.danger {
  background: #3a1a1a;
  border-color: #6a2a2a;
  color: #ff8888;
}

.action-btn.danger:hover {
  background: #5a1a1a;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: #222;
  border: 1px solid #444;
  border-radius: 8px;
  padding: 20px;
  min-width: 360px;
}

.modal h3 {
  font-size: 15px;
  font-weight: 600;
  margin-bottom: 16px;
}

.modal-label {
  display: block;
  font-size: 12px;
  color: #888;
  margin-bottom: 12px;
}

.modal-input {
  display: block;
  width: 100%;
  margin-top: 4px;
  background: #1a1a1a;
  border: 1px solid #444;
  color: #eee;
  padding: 6px 8px;
  border-radius: 4px;
  font-size: 13px;
}

.modal-actions {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
  margin-top: 16px;
}

.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 8px;
  background: #1a1a1a;
  border-top: 1px solid #333;
  flex-shrink: 0;
  font-size: 12px;
  color: #888;
}

.pagination button {
  background: #2a2a2a;
  border: 1px solid #444;
  color: #eee;
  padding: 3px 10px;
  border-radius: 3px;
  cursor: pointer;
  font-size: 12px;
}

.pagination button:disabled {
  opacity: 0.4;
  cursor: default;
}

.pagination button:hover:not(:disabled) {
  background: #3a3a3a;
}
</style>
