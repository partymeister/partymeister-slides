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
  family: string
}

export interface SaveTemplateData {
  name: string
  template_for: string
  definitions: string
  cached_html_preview: string
  cached_html_final: string
}
