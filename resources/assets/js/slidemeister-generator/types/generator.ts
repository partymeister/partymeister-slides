import type { SlideElement } from '@common/types/editor'

export interface GeneratedSlide {
  key: string
  type: string
  name: string
  elements: Record<string, SlideElement>
  html: string
  id?: number
}

export interface CompetitionData {
  competition: {
    id: number
    name: string
    competition_type: {
      is_anonymous: boolean
    }
  }
  templates: Record<string, {
    id: number
    definitions: string
  }>
  entries: EntryData[]
  participants: string[]
  videos: VideoData[]
}

export interface EntryData {
  id: number
  title: string
  author: string
  description: string
  remote_type: string
  filesize_human: string
  previous_sort_position: string
  previous_author: string
  previous_title: string
  options_string: string
  custom_option: string
  [key: string]: unknown
}

export interface VideoData {
  file_id: number
  preview: string
  data: Record<string, unknown>
}

// Timetable types

export interface TimetableRow {
  name: string
  type: string
  color: string
  time: string
}

export interface TimetableData {
  schedule: { id: number; name: string }
  template: { id: number; definitions: string }
  days: Record<string, TimetableRow[][]>  // day name → chunks of rows
}

// Prizegiving types

export interface PrizegivingRow {
  id: number
  title: string
  author: string
  remote_type: string
  rank: number
  points: number
  max_points: number
  tie: boolean
}

export interface PrizegivingCompetition {
  id: number
  name: string
  has_comment: boolean
  entries: PrizegivingRow[]
}

export interface PrizegivingData {
  results: Record<string, PrizegivingCompetition>
  specialVotes: PrizegivingRow[]
  comments: Record<string, string>
  templates: Record<string, { id: number; definitions: string }>
  config: { entries: number; bar_color: string }
}
