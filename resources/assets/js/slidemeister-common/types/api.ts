export interface SlideTemplateResponse {
  id: number
  name: string
  template_for: string
  definitions: string
  cached_html_preview: string | null
  cached_html_final: string | null
}

export interface ApiResponse<T> {
  data: T
  message?: string
}

export interface FontResponse {
  name: string
  path: string
  family: string
}

export interface SaveTemplateData {
  name: string
  template_for: string
  definitions: string
  cached_html_preview: string
  cached_html_final: string
}

export interface SlideResponse {
  id: number
  name: string
  slide_type: string
  category_id: number | null
  slide_template_id: number | null
  definitions: string
  cached_html_preview: string | null
  cached_html_final: string | null
}

export interface SaveSlideData {
  name: string
  slide_type?: string
  category_id?: number | null
  slide_template_id?: number | null
  definitions: string
  cached_html_preview: string
  cached_html_final: string
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}
