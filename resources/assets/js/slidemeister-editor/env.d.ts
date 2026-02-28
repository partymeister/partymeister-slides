/// <reference types="vite/client" />
declare module '*.vue' {
  import type { DefineComponent } from 'vue'
  const component: DefineComponent<{}, {}, any>
  export default component
}

interface Window {
  TOKEN: string
  BASE_URL: string
  TEMPLATE_ID: number | null
}
