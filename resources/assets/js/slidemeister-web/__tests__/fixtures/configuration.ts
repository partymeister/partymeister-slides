import type { SlideClientConfiguration, ServerConfiguration, JingleConfig } from '@/types/config'

export function createSlideClientConfig(
  overrides: Partial<SlideClientConfiguration> = {},
): SlideClientConfiguration {
  return {
    server: 'http://localhost',
    client: '1',
    prizegiving_bar_color: '#00ff00',
    prizegiving_bar_blink_color: '#ff0000',
    midi_note_jingle_1: 60,
    midi_note_jingle_2: 61,
    midi_note_jingle_3: 62,
    midi_note_jingle_4: 63,
    midi_note_jingle_5: 64,
    midi_note_jingle_6: 65,
    midi_note_jingle_7: 66,
    midi_note_jingle_8: 67,
    midi_note_jingle_9: 68,
    midi_note_jingle_10: 69,
    ...overrides,
  }
}

export function createServerConfig(overrides: Partial<ServerConfiguration> = {}): ServerConfiguration {
  return {
    key: 'test-key',
    host: 'localhost',
    port: 6001,
    path: '/ws',
    client: '1',
    ...overrides,
  }
}

export function createJingleConfig(): JingleConfig {
  return {
    jingle_1: 'http://localhost/audio/jingle1.mp3',
    jingle_2: 'http://localhost/audio/jingle2.mp3',
    jingle_3: null,
    jingle_4: null,
  }
}
