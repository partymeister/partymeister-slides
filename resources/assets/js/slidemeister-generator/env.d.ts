/// <reference types="vite/client" />

interface Window {
  TOKEN: string
  BASE_URL: string
  COMPETITION_ID: number
  SCHEDULE_ID: number
  EVENT_ID: number
  GENERATOR_TYPE: 'start' | 'competition' | 'timetable' | 'prizegiving' | 'event'
  HEADLESS?: boolean
}
