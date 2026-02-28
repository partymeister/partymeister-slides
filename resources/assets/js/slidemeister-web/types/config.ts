// types/config.ts

export interface SlideClientConfiguration {
  server: string
  client: string
  prizegiving_bar_color: string
  prizegiving_bar_blink_color: string
  midi_note_jingle_1: number
  midi_note_jingle_2: number
  midi_note_jingle_3: number
  midi_note_jingle_4: number
  midi_note_jingle_5: number
  midi_note_jingle_6: number
  midi_note_jingle_7: number
  midi_note_jingle_8: number
  midi_note_jingle_9: number
  midi_note_jingle_10: number
  [key: string]: string | number
}

export interface ServerConfiguration {
  key: string
  host: string
  port: number
  path: string
  client: string
}

export interface JingleConfig {
  jingle_1: string | null
  jingle_2: string | null
  jingle_3: string | null
  jingle_4: string | null
  [key: string]: string | null
}

export interface SlideClientApiResponse {
  data: {
    id: number
    name: string
    configuration: SlideClientConfiguration
    websocket: ServerConfiguration
    jingles: JingleConfig
  }
}
