import type {
  SlideTemplateResponse,
  ApiResponse,
  FontResponse,
  SaveTemplateData,
} from '@/types/api'

function baseUrl(): string {
  return (window.BASE_URL ?? '').replace(/\/+$/, '')
}

function headers(): HeadersInit {
  return {
    Authorization: `Bearer ${window.TOKEN}`,
    Accept: 'application/json',
    'Content-Type': 'application/json',
  }
}

async function request<T>(method: string, path: string, body?: unknown): Promise<T> {
  const url = `${baseUrl()}${path}`
  const init: RequestInit = { method, headers: headers() }
  if (body !== undefined) {
    init.body = JSON.stringify(body)
  }

  const response = await fetch(url, init)
  if (!response.ok) {
    const message = await response.text().catch(() => 'Unknown error')
    throw new Error(`API ${method} ${path} failed (${response.status}): ${message}`)
  }

  if (response.status === 204) {
    return undefined as T
  }

  return response.json() as Promise<T>
}

export function useApi() {
  async function getTemplate(id: number): Promise<SlideTemplateResponse> {
    const res = await request<ApiResponse<SlideTemplateResponse>>('GET', `/api/slide_templates/${id}`)
    return res.data
  }

  async function saveTemplate(id: number, data: SaveTemplateData): Promise<SlideTemplateResponse> {
    const res = await request<ApiResponse<SlideTemplateResponse>>('PUT', `/api/slide_templates/${id}`, data)
    return res.data
  }

  async function createTemplate(data: SaveTemplateData): Promise<SlideTemplateResponse> {
    const res = await request<ApiResponse<SlideTemplateResponse>>('POST', '/api/slide_templates', data)
    return res.data
  }

  async function deleteTemplate(id: number): Promise<void> {
    await request<void>('DELETE', `/api/slide_templates/${id}`)
  }

  async function listFonts(): Promise<FontResponse[]> {
    try {
      const res = await request<ApiResponse<FontResponse[]>>('GET', '/api/slidemeister/fonts')
      return res.data
    } catch (err) {
      if (err instanceof Error && err.message.includes('404')) {
        return []
      }
      throw err
    }
  }

  return {
    getTemplate,
    saveTemplate,
    createTemplate,
    deleteTemplate,
    listFonts,
  }
}
