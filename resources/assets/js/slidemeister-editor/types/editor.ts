export interface ElementCoordinates {
  transform: string
  width: number
  height: number
}

export interface PrizegivingBarCoordinates {
  x1: number
  x2: number
  y1: number
  y2: number
}

export interface SlideElementProperties {
  // Content
  content: string
  placeholder: string
  image: string | null
  dataUrl: string | null
  prettyname: string

  // Typography
  fontFamily: string
  fontSize: number
  fontKerning: string
  fontWeight: string
  fontStretch: string
  fontStyle: string
  letterSpacing: string
  calculatedFontSize: string

  // Text layout
  textAlign: string
  verticalAlign: string
  lineHeight: string
  textShadow: string
  textTransform: string

  // Colors
  color: string
  backgroundColor: string
  opacity: number

  // Behavior
  editable: boolean
  locked: boolean
  snapping: boolean
  resizable: boolean
  warpable: boolean
  visibility: string
  size: string

  // Position
  zIndex: number
  coordinates: ElementCoordinates

  // Special
  prizegivingbarCoordinates?: PrizegivingBarCoordinates
}

export interface SlideElement {
  name: string
  properties: SlideElementProperties
}

export type TemplateType =
  | 'basic'
  | 'coming_up'
  | 'now'
  | 'end'
  | 'competition'
  | 'competition_entry_1'
  | 'timetable'
  | 'participants'
  | 'prizegiving'
  | 'comments'
  | 'end_of_pg'

export interface ElementOrder {
  name: string
  zIndex: number
}

export interface SlideDefinitions {
  id: string
  type: string
  elements: Record<string, SlideElement>
}

export function createDefaultElement(name: string): SlideElement {
  return {
    name,
    properties: {
      content: '',
      placeholder: name,
      image: null,
      dataUrl: null,
      prettyname: name,

      fontFamily: 'Arial',
      fontSize: 30,
      fontKerning: 'auto',
      fontWeight: '400',
      fontStretch: '100',
      fontStyle: 'normal',
      letterSpacing: 'normal',
      calculatedFontSize: '30px',

      textAlign: 'left',
      verticalAlign: 'flex-start',
      lineHeight: '1.2',
      textShadow: 'none',
      textTransform: 'none',

      color: '#000000',
      backgroundColor: 'transparent',
      opacity: 1,

      editable: true,
      locked: false,
      snapping: true,
      resizable: true,
      warpable: false,
      visibility: 'render',
      size: 'individual',

      zIndex: 1,
      coordinates: {
        transform: 'matrix(1, 0, 0, 1, 0, 0) translate(0px, 0px)',
        width: 300,
        height: 200,
      },
    },
  }
}
