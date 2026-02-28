import type { PlaylistItem, SlideType } from '@/types/playlist'

let itemIdCounter = 0

export function createPlaylistItem(overrides: Partial<PlaylistItem> = {}): PlaylistItem {
  const id = ++itemIdCounter
  return {
    id,
    type: 'image',
    duration: 10,
    is_advanced_manually: false,
    midi_note: 0,
    callback_hash: '',
    callback_delay: 0,
    slide_type: '' as SlideType,
    metadata: null,
    slide: {
      id,
      cached_html_final: `<div>Slide ${id}</div>`,
    },
    file_association: {
      file: { url: `http://localhost/images/slide${id}.png` },
    },
    transition_slidemeister: { identifier: '0' },
    transition_duration: 2000,
    ...overrides,
  }
}

export function createVideoItem(overrides: Partial<PlaylistItem> = {}): PlaylistItem {
  return createPlaylistItem({
    type: 'video',
    file_association: { file: { url: 'http://localhost/videos/intro.mp4' } },
    slide: null,
    ...overrides,
  })
}

export function createManualItem(overrides: Partial<PlaylistItem> = {}): PlaylistItem {
  return createPlaylistItem({
    is_advanced_manually: true,
    duration: 0,
    ...overrides,
  })
}

export function createSiegmeisterItem(overrides: Partial<PlaylistItem> = {}): PlaylistItem {
  return createPlaylistItem({
    slide_type: 'siegmeister_bars',
    metadata: JSON.stringify([
      { x1: 0.1, y1: 0.2, x2: 0.8, y2: 0.3 },
      { x1: 0.1, y1: 0.4, x2: 0.6, y2: 0.5 },
      { x1: 0.1, y1: 0.6, x2: 0.9, y2: 0.7 },
    ]),
    ...overrides,
  })
}

export function createCompoItem(
  remoteType: 'party' | 'satellite' | 'remote' = 'party',
  overrides: Partial<PlaylistItem> = {},
): PlaylistItem {
  return createPlaylistItem({
    slide_type: 'compo',
    metadata: JSON.stringify({ remote_type: remoteType }),
    ...overrides,
  })
}

export function resetItemIdCounter(): void {
  itemIdCounter = 0
}
