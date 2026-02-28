// types/echo.ts

export interface EchoChannel {
  listen(event: string, callback: Function): this
  stopListening(event: string): this
}

export interface PlaylistRequestEvent {
  playlist_id: number
  callbacks: any
  callback_url: string
}

export interface PlaylistSeekRequestEvent {
  playlist_id: number
  index: number | false
}

export interface PlaylistNextRequestEvent {
  hard: boolean
}

export interface PlaylistPreviousRequestEvent {
  hard: boolean
}

export interface PlayNowItemPayload {
  playnow_type: 'slide' | 'image'
  type: 'image' | 'video'
  cached_html_final?: string
  file?: import('./playlist').SlideFile
  slide_type?: import('./playlist').SlideType
}

export interface PlayNowRequestEvent {
  item: PlayNowItemPayload
}

export interface SiegmeisterRequestEvent {}
