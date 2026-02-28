import { ref } from 'vue'
import type { CablesSlideData } from '@/types/cables'
import type { SlideType } from '@/types/playlist'

const SCENE_1_TYPES: SlideType[] = [
  'siegmeister_winners',
  'siegmeister_bars',
  'comments',
  'announce',
  'announce_important',
  'timetable',
]

const SCENE_2_LABELS: Partial<Record<SlideType, string>> = {
  comingup: 'COMING UP',
  now: 'NOW',
  end: 'END',
}

export function useCables() {
  const isReady = ref(false)
  let currentBackground: SlideType | null = null

  function cablesAvailable(): boolean {
    return typeof globalThis !== 'undefined'
      && (globalThis as any).CABLES?.patch?.setVariable !== undefined
  }

  function init(): void {
    isReady.value = cablesAvailable()
  }

  function setScene(scene: number, transition: boolean): void {
    const data: CablesSlideData = {
      scene,
      transition,
      time: Date.now(),
    }
    ;(globalThis as any).CABLES.patch.setVariable('currentSlide', data)
  }

  function setSlideType(type: SlideType): void {
    ;(globalThis as any).CABLES.patch.setVariable('SLIDETYPE', type)
  }

  function setCompetitionName(name: string): void {
    if (name.length > 8) {
      ;(globalThis as any).CABLES.patch.setVariable('eventOrCompoName', '')
      ;(globalThis as any).CABLES.patch.setVariable('eventOrCompoNameLong', name)
    } else {
      ;(globalThis as any).CABLES.patch.setVariable('eventOrCompoNameLong', '')
      ;(globalThis as any).CABLES.patch.setVariable('eventOrCompoName', name)
    }
  }

  function setSlideTypeString(str: string): void {
    ;(globalThis as any).CABLES.patch.setVariable('slideTypeString', str)
  }

  function setEntryType(type: string): void {
    const data: CablesSlideData = {
      scene: 3,
      transition: true,
      time: Date.now(),
      entryType: type,
    }
    ;(globalThis as any).CABLES.patch.setVariable('currentSlide', data)
  }

  function extractCompetitionName(html: string): string | null {
    const el = document.createElement('div')
    el.innerHTML = html
    const match = el.querySelector('[data-partymeister-slides-prettyname="competition"]')
    return match ? (match as HTMLElement).innerText || match.textContent || null : null
  }

  function updateForSlideType(
    slideType: SlideType,
    transition: boolean,
    html?: string | null,
    entryType?: string,
  ): void {
    let competitionName: string | null = null

    if (html && slideType !== 'compo') {
      competitionName = extractCompetitionName(html)
    }

    // Deduplication: skip if same background, no competition change, and not compo
    if (
      currentBackground === slideType
      && !competitionName
      && slideType !== 'compo'
    ) {
      currentBackground = slideType
      return
    }

    currentBackground = slideType

    setSlideType(slideType)

    if (SCENE_1_TYPES.includes(slideType)) {
      setScene(1, transition)
      return
    }

    const label = SCENE_2_LABELS[slideType]
    if (label) {
      setScene(2, transition)
      setSlideTypeString(label)
      if (competitionName) {
        setCompetitionName(competitionName)
      }
      return
    }

    if (slideType === 'compo') {
      const resolvedEntryType = entryType?.toLowerCase() || 'party'
      const data: CablesSlideData = {
        scene: 3,
        transition,
        time: Date.now(),
        entryType: resolvedEntryType,
      }
      ;(globalThis as any).CABLES.patch.setVariable('currentSlide', data)
      return
    }

    // Default: scene 0
    setScene(0, transition)
  }

  function resetBackground(): void {
    currentBackground = null
  }

  init()

  return {
    isReady,
    setScene,
    setSlideType,
    setCompetitionName,
    setSlideTypeString,
    setEntryType,
    extractCompetitionName,
    updateForSlideType,
    resetBackground,
  }
}
