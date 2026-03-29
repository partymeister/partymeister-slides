import { useEcho } from '@/composables/useEcho'
import { MockEcho, MockEchoChannel } from '@/__tests__/mocks/echo'
import type { ServerConfiguration } from '@/types/config'

const serverConfig: ServerConfiguration = {
  key: 'test-key',
  host: 'localhost',
  port: 6001,
  path: '/ws',
  client: 'client-42',
}

const CHANNEL = 'partymeister.slidemeister-web.client-42'

function createMocks() {
  const playlistStore = {
    cachePlaylist: vi.fn(),
    setActivePlaylist: vi.fn(() => true),
    playNow: false,
    exitPlayNow: vi.fn(),
    cachedPlaylists: [],
    currentPlaylist: null as any,
    currentItemIndex: null as number | null,
    getSavedPosition: vi.fn(() => undefined),
  }
  const connectionStore = {
    setConnected: vi.fn(),
    setDisconnected: vi.fn(),
    recordEvent: vi.fn(),
  }
  const engine = {
    seekToNext: vi.fn(),
    seekToPrevious: vi.fn(),
    seekToIndex: vi.fn(),
    seekToPlayNow: vi.fn(),
  }
  const siegmeisterTrigger = vi.fn()
  const storage = {
    load: vi.fn(() => Promise.resolve(null)),
    save: vi.fn(() => Promise.resolve()),
  }

  let mockEcho: MockEcho
  const echoFactory = (_config: ServerConfiguration) => {
    mockEcho = new MockEcho()
    return mockEcho
  }

  return {
    playlistStore,
    connectionStore,
    engine,
    siegmeisterTrigger,
    storage,
    echoFactory,
    getMockEcho: () => mockEcho!,
  }
}

function getChannel(mockEcho: MockEcho): MockEchoChannel {
  return mockEcho.channel(CHANNEL)
}

describe('useEcho', () => {
  beforeEach(() => {
    vi.useFakeTimers()
    // Mock fetch for PlaylistRequest handler
    vi.stubGlobal('fetch', vi.fn().mockResolvedValue({
      ok: true,
      json: () => Promise.resolve({
        data: { id: 1, name: 'Test', items: [] },
      }),
    }))
    ;(globalThis as any).BASE_URL = 'http://localhost'
    ;(globalThis as any).TOKEN = 'test-token'
  })

  afterEach(() => {
    vi.useRealTimers()
    vi.unstubAllGlobals()
    delete (globalThis as any).BASE_URL
    delete (globalThis as any).TOKEN
  })

  describe('connect', () => {
    it('should create echo instance and set connected state', async () => {
      const m = createMocks()
      const { connect, listening } = useEcho(
        m.playlistStore, m.connectionStore, m.engine,
        m.siegmeisterTrigger, m.storage, m.echoFactory,
      )

      await connect(serverConfig)

      expect(m.connectionStore.setConnected).toHaveBeenCalledWith(CHANNEL)
      expect(listening.value).toBe(true)
    })

    it('should set channel name based on config.client', async () => {
      const m = createMocks()
      const { connect } = useEcho(
        m.playlistStore, m.connectionStore, m.engine,
        m.siegmeisterTrigger, m.storage, m.echoFactory,
      )

      await connect(serverConfig)

      expect(m.connectionStore.setConnected).toHaveBeenCalledWith(
        'partymeister.slidemeister-web.client-42',
      )
    })
  })

  describe('disconnect', () => {
    it('should disconnect and set disconnected state', async () => {
      const m = createMocks()
      const { connect, disconnect } = useEcho(
        m.playlistStore, m.connectionStore, m.engine,
        m.siegmeisterTrigger, m.storage, m.echoFactory,
      )

      await connect(serverConfig)
      disconnect()

      expect(m.connectionStore.setDisconnected).toHaveBeenCalled()
    })

    it('should set listening to false', async () => {
      const m = createMocks()
      const { connect, disconnect, listening } = useEcho(
        m.playlistStore, m.connectionStore, m.engine,
        m.siegmeisterTrigger, m.storage, m.echoFactory,
      )

      await connect(serverConfig)
      expect(listening.value).toBe(true)

      disconnect()
      expect(listening.value).toBe(false)
    })
  })

  describe('PlaylistRequest', () => {
    it('should fetch playlist from API and cache it', async () => {
      // Use real timers for this test since fetch + fake timers interact poorly
      vi.useRealTimers()

      const playlist = { id: 7, name: 'Fetched', items: [] }
      vi.stubGlobal('fetch', vi.fn().mockResolvedValue({
        ok: true,
        json: () => Promise.resolve({ data: playlist }),
      }))

      const m = createMocks()
      const { connect } = useEcho(
        m.playlistStore, m.connectionStore, m.engine,
        m.siegmeisterTrigger, m.storage, m.echoFactory,
      )

      await connect(serverConfig)
      const channel = getChannel(m.getMockEcho())

      channel.emit('.Partymeister\\Slides\\Events\\PlaylistRequest', {
        playlist_id: 7,
        callbacks: false,
        callback_url: '',
      })

      // Flush all microtasks
      await new Promise(r => setTimeout(r, 10))

      expect(m.playlistStore.cachePlaylist).toHaveBeenCalledWith(
        expect.objectContaining({ id: 7, name: 'Fetched' }),
      )

      vi.useFakeTimers()
    })

    it('should record event on connectionStore', async () => {
      const m = createMocks()
      const { connect } = useEcho(
        m.playlistStore, m.connectionStore, m.engine,
        m.siegmeisterTrigger, m.storage, m.echoFactory,
      )

      await connect(serverConfig)
      const channel = getChannel(m.getMockEcho())

      channel.emit('.Partymeister\\Slides\\Events\\PlaylistRequest', {
        playlist_id: 1,
        callbacks: false,
        callback_url: '',
      })

      expect(m.connectionStore.recordEvent).toHaveBeenCalled()
    })
  })

  describe('PlaylistSeekRequest', () => {
    it('should activate playlist and seek to index', async () => {
      const m = createMocks()
      const { connect } = useEcho(
        m.playlistStore, m.connectionStore, m.engine,
        m.siegmeisterTrigger, m.storage, m.echoFactory,
      )

      await connect(serverConfig)
      const channel = getChannel(m.getMockEcho())

      channel.emit('.Partymeister\\Slides\\Events\\PlaylistSeekRequest', {
        playlist_id: 5,
        index: 3,
      })

      // Allow async handler to resolve
      await vi.runAllTimersAsync()

      expect(m.playlistStore.setActivePlaylist).toHaveBeenCalledWith(5)
      expect(m.engine.seekToIndex).toHaveBeenCalledWith(3)
    })

    it('should load from storage when index is false', async () => {
      const m = createMocks()
      m.storage.load.mockResolvedValue(7)

      const { connect } = useEcho(
        m.playlistStore, m.connectionStore, m.engine,
        m.siegmeisterTrigger, m.storage, m.echoFactory,
      )

      await connect(serverConfig)
      const channel = getChannel(m.getMockEcho())

      channel.emit('.Partymeister\\Slides\\Events\\PlaylistSeekRequest', {
        playlist_id: 5,
        index: false,
      })

      await vi.runAllTimersAsync()

      expect(m.storage.load).toHaveBeenCalledWith('currentItem')
      expect(m.engine.seekToIndex).toHaveBeenCalledWith(7)
    })

    it('should do nothing when playlist not in cache', async () => {
      const m = createMocks()
      m.playlistStore.setActivePlaylist.mockReturnValue(false)

      const { connect } = useEcho(
        m.playlistStore, m.connectionStore, m.engine,
        m.siegmeisterTrigger, m.storage, m.echoFactory,
      )

      await connect(serverConfig)
      const channel = getChannel(m.getMockEcho())

      channel.emit('.Partymeister\\Slides\\Events\\PlaylistSeekRequest', {
        playlist_id: 99,
        index: 0,
      })

      await vi.runAllTimersAsync()

      expect(m.engine.seekToIndex).not.toHaveBeenCalled()
    })
  })

  describe('PlaylistNextRequest', () => {
    it('should call engine.seekToNext with hard flag', async () => {
      const m = createMocks()
      const { connect } = useEcho(
        m.playlistStore, m.connectionStore, m.engine,
        m.siegmeisterTrigger, m.storage, m.echoFactory,
      )

      await connect(serverConfig)
      const channel = getChannel(m.getMockEcho())

      channel.emit('.Partymeister\\Slides\\Events\\PlaylistNextRequest', { hard: true })

      expect(m.engine.seekToNext).toHaveBeenCalledWith(true)
    })

    it('should exit playNow if active', async () => {
      const m = createMocks()
      m.playlistStore.playNow = true

      const { connect } = useEcho(
        m.playlistStore, m.connectionStore, m.engine,
        m.siegmeisterTrigger, m.storage, m.echoFactory,
      )

      await connect(serverConfig)
      const channel = getChannel(m.getMockEcho())

      channel.emit('.Partymeister\\Slides\\Events\\PlaylistNextRequest', { hard: false })

      expect(m.playlistStore.exitPlayNow).toHaveBeenCalled()
      expect(m.engine.seekToNext).toHaveBeenCalledWith(false)
    })
  })

  describe('PlaylistPreviousRequest', () => {
    it('should call engine.seekToPrevious with hard flag', async () => {
      const m = createMocks()
      const { connect } = useEcho(
        m.playlistStore, m.connectionStore, m.engine,
        m.siegmeisterTrigger, m.storage, m.echoFactory,
      )

      await connect(serverConfig)
      const channel = getChannel(m.getMockEcho())

      channel.emit('.Partymeister\\Slides\\Events\\PlaylistPreviousRequest', { hard: true })

      expect(m.engine.seekToPrevious).toHaveBeenCalledWith(true)
    })
  })

  describe('PlayNowRequest', () => {
    it('should build item from slide payload and call engine.seekToPlayNow', async () => {
      const m = createMocks()
      const { connect } = useEcho(
        m.playlistStore, m.connectionStore, m.engine,
        m.siegmeisterTrigger, m.storage, m.echoFactory,
      )

      await connect(serverConfig)
      const channel = getChannel(m.getMockEcho())

      channel.emit('.Partymeister\\Slides\\Events\\PlayNowRequest', {
        item: {
          playnow_type: 'slide',
          type: 'image',
          cached_html_final: '<div>Hello</div>',
          slide_type: 'announce',
        },
      })

      expect(m.engine.seekToPlayNow).toHaveBeenCalledWith(
        expect.objectContaining({
          type: 'image',
          duration: 20,
          is_advanced_manually: true,
          slide_type: 'announce',
          slide: expect.objectContaining({
            cached_html_final: '<div>Hello</div>',
          }),
          file_association: null,
          transition_duration: 2000,
        }),
      )
    })

    it('should build item from image payload with file_association', async () => {
      const m = createMocks()
      const { connect } = useEcho(
        m.playlistStore, m.connectionStore, m.engine,
        m.siegmeisterTrigger, m.storage, m.echoFactory,
      )

      await connect(serverConfig)
      const channel = getChannel(m.getMockEcho())

      const file = { url: 'https://example.com/image.png' }
      channel.emit('.Partymeister\\Slides\\Events\\PlayNowRequest', {
        item: {
          playnow_type: 'image',
          type: 'image',
          file,
        },
      })

      expect(m.engine.seekToPlayNow).toHaveBeenCalledWith(
        expect.objectContaining({
          type: 'image',
          slide: null,
          file_association: { file },
        }),
      )
    })
  })

  describe('SiegmeisterRequest', () => {
    it('should call siegmeisterTrigger', async () => {
      const m = createMocks()
      const { connect } = useEcho(
        m.playlistStore, m.connectionStore, m.engine,
        m.siegmeisterTrigger, m.storage, m.echoFactory,
      )

      await connect(serverConfig)
      const channel = getChannel(m.getMockEcho())

      channel.emit('.Partymeister\\Slides\\Events\\SiegmeisterRequest', {})

      expect(m.siegmeisterTrigger).toHaveBeenCalled()
    })

    it('should debounce with 2000ms cooldown', async () => {
      const m = createMocks()
      const { connect } = useEcho(
        m.playlistStore, m.connectionStore, m.engine,
        m.siegmeisterTrigger, m.storage, m.echoFactory,
      )

      await connect(serverConfig)
      const channel = getChannel(m.getMockEcho())

      // First call goes through
      channel.emit('.Partymeister\\Slides\\Events\\SiegmeisterRequest', {})
      expect(m.siegmeisterTrigger).toHaveBeenCalledTimes(1)

      // Second call within 2s is suppressed
      channel.emit('.Partymeister\\Slides\\Events\\SiegmeisterRequest', {})
      expect(m.siegmeisterTrigger).toHaveBeenCalledTimes(1)

      // After 2s cooldown, calls go through again
      vi.advanceTimersByTime(2000)
      channel.emit('.Partymeister\\Slides\\Events\\SiegmeisterRequest', {})
      expect(m.siegmeisterTrigger).toHaveBeenCalledTimes(2)
    })
  })
})
