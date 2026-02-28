<template>
  <div class="media-panel">
    <h3 class="panel-title">Media</h3>

    <div class="media-controls">
      <select v-model="categoryId" @change="loadFiles(1)" class="media-select">
        <option value="">All categories</option>
        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.label }}</option>
      </select>
    </div>

    <div v-if="loading" class="media-loading">Loading...</div>

    <div v-else class="media-grid">
      <div
        v-for="file in files"
        :key="file.id"
        class="media-item"
        :class="{ 'not-image': !file.is_image }"
        draggable="true"
        @dragstart="onDragStart($event, file)"
        :title="file.description || file.name"
      >
        <img
          v-if="file.thumb_url"
          :src="file.thumb_url"
          class="media-thumb"
          loading="lazy"
        />
        <div v-else class="media-thumb media-placeholder">
          <span>{{ file.mime_type || 'File' }}</span>
        </div>
        <div class="media-label">{{ file.name }}</div>
      </div>
    </div>

    <div v-if="!loading && files.length === 0" class="media-empty">No files found</div>

    <div v-if="pagination && pagination.last_page > 1" class="media-pagination">
      <button :disabled="pagination.current_page <= 1" @click="loadFiles(pagination.current_page - 1)">&laquo;</button>
      <span>{{ pagination.current_page }} / {{ pagination.last_page }}</span>
      <button :disabled="pagination.current_page >= pagination.last_page" @click="loadFiles(pagination.current_page + 1)">&raquo;</button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

interface FileItem {
  id: number
  name: string
  description: string
  mime_type: string
  is_image: boolean
  url: string | null
  thumb_url: string | null
}

interface Pagination {
  current_page: number
  last_page: number
}

const emit = defineEmits<{
  'apply-image': [url: string]
}>()

interface CategoryOption {
  id: number
  label: string
}

const categories = ref<CategoryOption[]>([])
const categoryId = ref('')
const files = ref<FileItem[]>([])
const pagination = ref<Pagination | null>(null)
const loading = ref(false)

function apiHeaders(): HeadersInit {
  return {
    Authorization: `Bearer ${window.TOKEN}`,
    Accept: 'application/json',
  }
}

function baseUrl(): string {
  return (window.BASE_URL ?? '').replace(/\/+$/, '')
}

function isImageMime(mime: string): boolean {
  return /^image\/(png|jpe?g|gif|svg|webp|bmp)$/i.test(mime)
}

function flattenCategories(nodes: any[], depth = 0): CategoryOption[] {
  const result: CategoryOption[] = []
  for (const node of nodes) {
    const indent = '\u00A0\u00A0'.repeat(depth)
    result.push({ id: node.id, label: `${indent}${node.name}` })
    if (node.children && node.children.length > 0) {
      result.push(...flattenCategories(node.children, depth + 1))
    }
  }
  return result
}

async function loadCategories(): Promise<void> {
  try {
    const res = await fetch(
      `${baseUrl()}/api/category_trees?scope=slides`,
      { headers: apiHeaders() },
    )
    if (!res.ok) return
    const json = await res.json()
    // Only show subcategories of the "Media" root category
    const allTrees = json.data || []
    const mediaTree = allTrees.find((t: any) => /^media$/i.test(t.name))
    categories.value = mediaTree?.children
      ? flattenCategories(mediaTree.children)
      : flattenCategories(allTrees)
  } catch { /* ignore */ }
}

async function loadFiles(page = 1): Promise<void> {
  loading.value = true
  try {
    let url = `${baseUrl()}/api/files?page=${page}`
    if (categoryId.value) {
      url += `&category_id=${categoryId.value}`
    }
    const res = await fetch(url, { headers: apiHeaders() })
    if (!res.ok) {
      files.value = []
      return
    }
    const json = await res.json()
    files.value = (json.data || [])
      .filter((f: any) => f.exists !== false)
      .map((f: any) => {
        const media = f.file
        const mime = media?.mime_type || ''
        const fileUrl = media?.url || null
        const thumbUrl = media?.conversions?.preview || media?.conversions?.thumb || null
        return {
          id: f.id,
          name: media?.name || f.description || `File ${f.id}`,
          description: f.description || '',
          mime_type: mime,
          is_image: isImageMime(mime),
          url: fileUrl,
          thumb_url: isImageMime(mime) ? (thumbUrl || fileUrl) : null,
        }
      })
    pagination.value = json.meta
      ? { current_page: json.meta.current_page, last_page: json.meta.last_page }
      : null
  } catch {
    files.value = []
  } finally {
    loading.value = false
  }
}

function onDragStart(event: DragEvent, file: FileItem): void {
  if (file.url && event.dataTransfer) {
    event.dataTransfer.setData('text/uri-list', file.url)
    event.dataTransfer.setData('application/x-slidemeister-media', JSON.stringify({
      url: file.url,
      name: file.name,
    }))
    event.dataTransfer.effectAllowed = 'copy'
  }
}


onMounted(() => {
  loadCategories()
  loadFiles()
})
</script>

<style scoped>
.media-panel {
  border-top: 1px solid #333;
  padding: 8px;
  display: flex;
  flex-direction: column;
  min-height: 0;
  flex: 1;
  overflow: hidden;
}

.panel-title {
  font-size: 12px;
  text-transform: uppercase;
  color: #888;
  margin: 0 0 8px;
}

.media-controls {
  margin-bottom: 8px;
}

.media-select {
  width: 100%;
  padding: 4px 6px;
  background: #2a2a2a;
  border: 1px solid #555;
  border-radius: 4px;
  color: #ddd;
  font-size: 12px;
  cursor: pointer;
}

.media-select:focus {
  outline: none;
  border-color: #7aa2f7;
}

.media-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 6px;
  overflow-y: auto;
  flex: 1;
  min-height: 0;
}

.media-item {
  cursor: grab;
  border: 1px solid #333;
  border-radius: 4px;
  overflow: hidden;
  background: #252525;
  transition: border-color 0.15s;
}

.media-item:hover {
  border-color: #4a9eff;
}

.media-item.not-image {
  opacity: 0.5;
  cursor: default;
}

.media-thumb {
  width: 100%;
  aspect-ratio: 16 / 9;
  object-fit: cover;
  display: block;
  background: #1a1a1a;
}

.media-placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  color: #555;
  font-size: 10px;
}

.media-label {
  padding: 4px 6px;
  font-size: 11px;
  color: #aaa;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.media-loading,
.media-empty {
  text-align: center;
  color: #666;
  font-style: italic;
  padding: 16px 0;
  font-size: 12px;
}

.media-pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding-top: 8px;
  font-size: 12px;
  color: #888;
  flex-shrink: 0;
}

.media-pagination button {
  background: #2a2a2a;
  border: 1px solid #444;
  color: #eee;
  padding: 2px 8px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
}

.media-pagination button:hover:not(:disabled) {
  background: #3a3a3a;
}

.media-pagination button:disabled {
  opacity: 0.4;
  cursor: default;
}
</style>
