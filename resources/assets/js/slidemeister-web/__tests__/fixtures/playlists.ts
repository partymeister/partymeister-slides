import type { Playlist } from '@/types/playlist'
import { createPlaylistItem } from './items'

let playlistIdCounter = 0

export function createPlaylist(overrides: Partial<Playlist> = {}): Playlist {
  const id = ++playlistIdCounter
  return {
    id,
    name: `Test Playlist ${id}`,
    callbacks: true,
    callback_url: 'http://localhost/callback/',
    updated_at: '2025-01-01T00:00:00.000000Z',
    items: [createPlaylistItem(), createPlaylistItem(), createPlaylistItem()],
    ...overrides,
  }
}

export function resetPlaylistIdCounter(): void {
  playlistIdCounter = 0
}
