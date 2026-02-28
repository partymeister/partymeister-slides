// types/cables.ts

export interface CablesSlideData {
  scene: number
  transition: boolean
  time: number
  entryType?: string
}

export interface CablesPatch {
  setVariable(name: string, value: unknown): void
}

export interface CablesGlobal {
  patch: CablesPatch
}

declare global {
  interface Window {
    CABLES: CablesGlobal
  }
}
