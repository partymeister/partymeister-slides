// __tests__/unit/stores/configStore.test.ts
import { setActivePinia, createPinia } from 'pinia'
import { useConfigStore } from '@/stores/configStore'
import { createSlideClientConfig, createServerConfig, createJingleConfig } from '../../fixtures/configuration'

describe('configStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('should have null initial state', () => {
    const store = useConfigStore()
    expect(store.slideClientConfig).toBeNull()
    expect(store.serverConfig).toBeNull()
    expect(store.jingles).toBeNull()
  })

  it('should store slide client configuration', () => {
    const store = useConfigStore()
    const config = createSlideClientConfig()
    store.setSlideClientConfig(config)
    expect(store.slideClientConfig).toEqual(config)
  })

  it('should store server configuration', () => {
    const store = useConfigStore()
    const config = createServerConfig()
    store.setServerConfig(config)
    expect(store.serverConfig).toEqual(config)
  })

  it('should store jingle configuration', () => {
    const store = useConfigStore()
    const jingles = createJingleConfig()
    store.setJingles(jingles)
    expect(store.jingles).toEqual(jingles)
  })

  it('should return midi note for a given jingle index', () => {
    const store = useConfigStore()
    store.setSlideClientConfig(createSlideClientConfig({ midi_note_jingle_3: 72 }))
    expect(store.getMidiNote(3)).toBe(72)
  })

  it('should return 0 for unconfigured midi note', () => {
    const store = useConfigStore()
    expect(store.getMidiNote(1)).toBe(0)
  })

  it('should return prizegiving bar color from config', () => {
    const store = useConfigStore()
    store.setSlideClientConfig(createSlideClientConfig({ prizegiving_bar_color: '#ff0000' }))
    expect(store.prizegivingBarColor).toBe('#ff0000')
  })

  it('should return default bar color when no config', () => {
    const store = useConfigStore()
    expect(store.prizegivingBarColor).toBe('#00ff00')
  })

  it('should return prizegiving bar blink color from config', () => {
    const store = useConfigStore()
    store.setSlideClientConfig(createSlideClientConfig({ prizegiving_bar_blink_color: '#0000ff' }))
    expect(store.prizegivingBarBlinkColor).toBe('#0000ff')
  })

  it('should return default bar blink color when no config', () => {
    const store = useConfigStore()
    expect(store.prizegivingBarBlinkColor).toBe('#ff0000')
  })
})
