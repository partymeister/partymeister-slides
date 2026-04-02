// types/playlist.ts

export type SlideType =
  | ''
  | 'siegmeister_winners'
  | 'siegmeister_bars'
  | 'comingup'
  | 'now'
  | 'end'
  | 'comments'
  | 'announce'
  | 'announce_important'
  | 'compo'
  | 'timetable'

export type RemoteType = 'party' | 'satellite' | 'remote'

export interface SlideFile {
  url: string
}

export interface FileAssociation {
  file: SlideFile
}

export interface Slide {
  id: number
  cached_html_final: string
}

export interface TransitionConfig {
  identifier: string
}

export interface PlaylistItem {
  id: number
  type: 'image' | 'video'
  duration: number
  is_advanced_manually: boolean
  midi_note: number
  callback_hash: string
  callback_delay: number
  slide_type: SlideType
  metadata: string | CompoMetadata | SiegmeisterMetadata[] | null
  slide: Slide | null
  file_association: FileAssociation | null
  transition_slidemeister: TransitionConfig | null
  transition_duration: number
}

export interface CompoMetadata {
  remote_type: RemoteType
}

export interface SiegmeisterBarData {
  x1: number
  y1: number
  x2: number
  y2: number
}

export type SiegmeisterMetadata = SiegmeisterBarData

export interface PlaylistTimestamp {
  date: string
}

export interface Playlist {
  id: number
  name: string
  callbacks: boolean
  callback_url: string
  updated_at: PlaylistTimestamp | string
  items: PlaylistItem[]
}

export interface PlayNowPayload {
  playnow_type: 'slide' | 'image'
  type: 'image' | 'video'
  cached_html_final?: string
  file?: SlideFile
  slide_type?: SlideType
}
