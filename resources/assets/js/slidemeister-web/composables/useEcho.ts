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
) {
  let echoInstance: EchoInstance | null = null
  let channelName: string | null = null
  const listening = ref(false)
  let siegmeisterInProgress = false

  async function connect(config: ServerConfiguration): Promise<void> {
    if (echoInstance) disconnect()

    const factory = echoFactory ?? createDefaultEcho
    echoInstance = await factory(config)
    channelName = `partymeister.slidemeister-web.${config.client}`

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

    // PlaylistRequest - cache or update playlist, persist to storage
    channel.listen('.Partymeister\\Slides\\Events\\PlaylistRequest', async (e: PlaylistRequestEvent) => {
      connectionStore.recordEvent()
      playlistStore.cachePlaylist(e.playlist)

      // Persist cached playlists to IndexedDB for crash recovery
      await storage.save('cachedPlaylists', playlistStore.cachedPlaylists)

      // If this is the active playlist, persist it too
      if (playlistStore.currentPlaylist?.id === e.playlist.id) {
        await storage.save('playlist', playlistStore.currentPlaylist)
        await storage.save('currentItem', playlistStore.currentItemIndex)
      }
    })

    // PlaylistSeekRequest - switch to playlist and seek
    channel.listen('.Partymeister\\Slides\\Events\\PlaylistSeekRequest', async (e: PlaylistSeekRequestEvent) => {
      connectionStore.recordEvent()
      const found = playlistStore.setActivePlaylist(e.playlist_id)
      if (!found) return

      // Persist active playlist to storage
      await storage.save('playlist', playlistStore.currentPlaylist)

      let targetIndex = e.index
      if (targetIndex === false) {
        const stored = await storage.load<number>('currentItem')
        targetIndex = stored ?? 0
      }
      engine.seekToIndex(targetIndex as number)
    })

    // PlaylistNextRequest
    channel.listen('.Partymeister\\Slides\\Events\\PlaylistNextRequest', (e: PlaylistNextRequestEvent) => {
      connectionStore.recordEvent()
      if (playlistStore.playNow) {
        playlistStore.exitPlayNow()
      }
      engine.seekToNext(e.hard)
    })

    // PlaylistPreviousRequest
    channel.listen('.Partymeister\\Slides\\Events\\PlaylistPreviousRequest', (e: PlaylistPreviousRequestEvent) => {
      connectionStore.recordEvent()
      if (playlistStore.playNow) {
        playlistStore.exitPlayNow()
      }
      engine.seekToPrevious(e.hard)
    })

    // PlayNowRequest - payload wraps item in e.item
    channel.listen('.Partymeister\\Slides\\Events\\PlayNowRequest', (e: PlayNowRequestEvent) => {
      connectionStore.recordEvent()
      const item = buildPlayNowItem(e.item)
      engine.seekToPlayNow(item)
    })

    // SiegmeisterRequest (2s debounce)
    channel.listen('.Partymeister\\Slides\\Events\\SiegmeisterRequest', (_e: SiegmeisterRequestEvent) => {
      connectionStore.recordEvent()
      if (siegmeisterInProgress) return
      siegmeisterInProgress = true
      siegmeisterTrigger()
      setTimeout(() => { siegmeisterInProgress = false }, 2000)
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
