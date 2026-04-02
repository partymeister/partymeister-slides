import { ref } from 'vue'
import type { ServerConfiguration } from '@/types/config'
import type {
  PlaylistRequestEvent,
  PlaylistSeekRequestEvent,
  PlaylistNextRequestEvent,
  PlaylistPreviousRequestEvent,
  PlayNowRequestEvent,
  PlayNowItemPayload,
  SiegmeisterRequestEvent,
} from '@/types/echo'
import type { PlaylistItem } from '@/types/playlist'

interface EchoInstance {
  channel(name: string): any
  leave(name: string): void
  disconnect(): void
  connector?: { pusher?: { connection?: { state?: string } } }
}

type EchoFactory = (config: ServerConfiguration) => EchoInstance

export function useEcho(
  playlistStore: /* ReturnType<typeof usePlaylistStore> */ any,
  connectionStore: /* ReturnType<typeof useConnectionStore> */ any,
  engine: /* ReturnType<typeof usePlaylistEngine> */ any,
  siegmeisterTrigger: () => void,
  storage: /* ReturnType<typeof useStorage> */ any,
  echoFactory?: EchoFactory,
  logEvent?: (type: 'socket' | 'transition' | 'midi' | 'error', message: string) => void,
) {
  let echoInstance: EchoInstance | null = null
  let channelName: string | null = null
  const listening = ref(false)
  let siegmeisterInProgress = false
  const pendingFetches = new Map<number, Promise<void>>()

  async function connect(config: ServerConfiguration): Promise<void> {
    if (echoInstance) disconnect()

    const factory = echoFactory ?? createDefaultEcho
    console.log('[Echo] Connecting with config:', config)
    echoInstance = await factory(config)
    channelName = `partymeister.slidemeister-web.${config.client}`
    console.log('[Echo] Listening on channel:', channelName)

    addListeners()
    connectionStore.setConnected(channelName)
    listening.value = true
  }

  function disconnect(): void {
    if (echoInstance && channelName) {
      echoInstance.leave(channelName)
    }
    if (echoInstance) {
      echoInstance.disconnect()
    }
    echoInstance = null
    channelName = null
    listening.value = false
    connectionStore.setDisconnected()
  }

  function addListeners(): void {
    if (!echoInstance || !channelName) return
    const channel = echoInstance.channel(channelName)

    // PlaylistRequest - fetch playlist from API, cache it, persist to storage
    channel.listen('.Partymeister\\Slides\\Events\\PlaylistRequest', (e: PlaylistRequestEvent) => {
      console.log('[Echo] PlaylistRequest', e)
      connectionStore.recordEvent()
      logEvent?.('socket', `PlaylistRequest #${e.playlist_id}`)

      const fetchPromise = (async () => {
        // Fetch full playlist data from REST API with timeout
        const baseUrl = (globalThis as any).BASE_URL
        const token = (globalThis as any).TOKEN
        const controller = new AbortController()
        const timeoutId = setTimeout(() => controller.abort(), 10000)

        try {
          const response = await fetch(`${baseUrl}/api/playlists/${e.playlist_id}`, {
            headers: { Authorization: `Bearer ${token}` },
            signal: controller.signal,
          })
          clearTimeout(timeoutId)

          if (!response.ok) {
            console.error('[Echo] Failed to fetch playlist', e.playlist_id, response.status)
            logEvent?.('error', `Fetch playlist #${e.playlist_id} failed: ${response.status}`)
            return
          }
          const json = await response.json()
          const playlist = json.data
          playlist.callbacks = e.callbacks
          playlist.callback_url = e.callback_url

          playlistStore.cachePlaylist(playlist)
          console.log('[Echo] Playlist fetched and cached', e.playlist_id)

          // Persist cached playlists to IndexedDB for crash recovery
          await storage.save('cachedPlaylists', playlistStore.cachedPlaylists)

          // If this is the active playlist, persist it too
          if (playlistStore.currentPlaylist?.id === playlist.id) {
            await storage.save('playlist', playlistStore.currentPlaylist)
            await storage.save('currentItem', playlistStore.currentItemIndex)
          }
        } catch (fetchErr) {
          clearTimeout(timeoutId)
          throw fetchErr
        }
      })().catch(err => {
        console.error('[Echo] PlaylistRequest handler error:', err)
        logEvent?.('error', `PlaylistRequest error: ${err?.message ?? err}`)
      }).finally(() => {
        pendingFetches.delete(e.playlist_id)
      })

      pendingFetches.set(e.playlist_id, fetchPromise)
    })

    // PlaylistSeekRequest - switch to playlist and seek
    channel.listen('.Partymeister\\Slides\\Events\\PlaylistSeekRequest', async (e: PlaylistSeekRequestEvent) => {
      try {
        console.log('[Echo] PlaylistSeekRequest', e)
        connectionStore.recordEvent()
        logEvent?.('socket', `SeekRequest playlist #${e.playlist_id} index=${e.index}`)

        // Wait for any in-flight fetch for this playlist to complete (with timeout)
        const pending = pendingFetches.get(e.playlist_id)
        if (pending) {
          console.log('[Echo] Waiting for playlist fetch to complete...', e.playlist_id)
          const timeout = new Promise<void>(resolve => setTimeout(resolve, 12000))
          await Promise.race([pending, timeout])
          if (pendingFetches.has(e.playlist_id)) {
            console.warn('[Echo] Playlist fetch timed out, proceeding anyway', e.playlist_id)
            logEvent?.('error', `Fetch timeout for playlist #${e.playlist_id}`)
            pendingFetches.delete(e.playlist_id)
          }
        }

        const found = playlistStore.setActivePlaylist(e.playlist_id)
        if (!found) {
          console.warn('[Echo] PlaylistSeekRequest: playlist not found in cache', e.playlist_id)
          return
        }

        // Persist active playlist to storage
        await storage.save('playlist', playlistStore.currentPlaylist)

        let targetIndex = e.index
        if (targetIndex === false) {
          // Check per-playlist saved position first, then fall back to storage
          const savedPos = playlistStore.getSavedPosition(e.playlist_id)
          if (savedPos !== undefined) {
            targetIndex = savedPos
          } else {
            const stored = await storage.load<number>('currentItem')
            targetIndex = stored ?? 0
          }
        }
        engine.seekToIndex(targetIndex as number)
      } catch (err) {
        console.error('[Echo] PlaylistSeekRequest handler error:', err)
      }
    })

    // PlaylistNextRequest
    channel.listen('.Partymeister\\Slides\\Events\\PlaylistNextRequest', (e: PlaylistNextRequestEvent) => {
      try {
        console.log('[Echo] PlaylistNextRequest', e)
        connectionStore.recordEvent()
        logEvent?.('socket', `NextRequest (${e.hard ? 'hard' : 'soft'})`)
        if (playlistStore.playNow) {
          playlistStore.exitPlayNow()
        }
        engine.seekToNext(e.hard)
      } catch (err) {
        console.error('[Echo] PlaylistNextRequest handler error:', err)
      }
    })

    // PlaylistPreviousRequest
    channel.listen('.Partymeister\\Slides\\Events\\PlaylistPreviousRequest', (e: PlaylistPreviousRequestEvent) => {
      try {
        console.log('[Echo] PlaylistPreviousRequest', e)
        connectionStore.recordEvent()
        logEvent?.('socket', `PreviousRequest (${e.hard ? 'hard' : 'soft'})`)
        if (playlistStore.playNow) {
          playlistStore.exitPlayNow()
        }
        engine.seekToPrevious(e.hard)
      } catch (err) {
        console.error('[Echo] PlaylistPreviousRequest handler error:', err)
      }
    })

    // PlayNowRequest - payload wraps item in e.item
    channel.listen('.Partymeister\\Slides\\Events\\PlayNowRequest', async (e: PlayNowRequestEvent) => {
      try {
        console.log('[Echo] PlayNowRequest', e)
        connectionStore.recordEvent()
        logEvent?.('socket', `PlayNow (${e.item.playnow_type})`)

        // For slides, fetch full data from API (payload only contains slide_id to stay under WS size limit)
        if (e.item.playnow_type === 'slide' && e.item.slide_id) {
          const baseUrl = (globalThis as any).BASE_URL
          const token = (globalThis as any).TOKEN
          const response = await fetch(`${baseUrl}/api/slides/${e.item.slide_id}`, {
            headers: { Authorization: `Bearer ${token}` },
          })
          if (!response.ok) {
            console.error('[Echo] Failed to fetch slide for PlayNow', e.item.slide_id, response.status)
            logEvent?.('error', `PlayNow slide fetch failed: ${response.status}`)
            return
          }
          const json = await response.json()
          const slide = json.data
          e.item.slide_type = slide.slide_type ?? ''
          e.item.cached_html_final = slide.cached_html_final ?? ''
        }

        const item = buildPlayNowItem(e.item)
        engine.seekToPlayNow(item)
      } catch (err) {
        console.error('[Echo] PlayNowRequest handler error:', err)
      }
    })

    // SiegmeisterRequest (2s debounce)
    channel.listen('.Partymeister\\Slides\\Events\\SiegmeisterRequest', (_e: SiegmeisterRequestEvent) => {
      try {
        console.log('[Echo] SiegmeisterRequest', _e)
        connectionStore.recordEvent()
        logEvent?.('socket', 'SiegmeisterRequest')
        if (siegmeisterInProgress) return
        siegmeisterInProgress = true
        siegmeisterTrigger()
        setTimeout(() => { siegmeisterInProgress = false }, 2000)
      } catch (err) {
        console.error('[Echo] SiegmeisterRequest handler error:', err)
      }
    })
  }

  return { connect, disconnect, listening }
}

function buildPlayNowItem(payload: PlayNowItemPayload): PlaylistItem {
  const isSlide = payload.playnow_type === 'slide'
  return {
    id: Date.now(),
    type: payload.type,
    duration: 20,
    is_advanced_manually: true,
    midi_note: 0,
    callback_hash: '',
    callback_delay: 0,
    slide_type: payload.slide_type ?? '',
    metadata: null,
    slide: isSlide ? {
      id: Date.now(),
      cached_html_final: payload.cached_html_final ?? '',
    } : null,
    file_association: !isSlide && payload.file ? { file: payload.file } : null,
    transition_slidemeister: { identifier: '255' },
    transition_duration: 2000,
  }
}

async function createDefaultEcho(config: ServerConfiguration): Promise<any> {
  const { default: Echo } = await import('laravel-echo')
  const Pusher = (await import('pusher-js')).default
  return new Echo({
    broadcaster: 'pusher',
    key: config.key,
    cluster: 'mt1',
    wsHost: config.host,
    wsPort: config.port,
    wsPath: config.path,
    forceTLS: false,
    disableStats: true,
    enabledTransports: ['ws'],
    Pusher,
  })
}
