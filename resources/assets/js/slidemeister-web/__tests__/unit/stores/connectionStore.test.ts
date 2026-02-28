// __tests__/unit/stores/connectionStore.test.ts
import { setActivePinia, createPinia } from 'pinia'
import { useConnectionStore } from '@/stores/connectionStore'

describe('connectionStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('should start disconnected with no error', () => {
    const store = useConnectionStore()
    expect(store.isConnected).toBe(false)
    expect(store.error).toBeNull()
    expect(store.channelName).toBeNull()
  })

  it('should track connection state', () => {
    const store = useConnectionStore()
    store.setConnected('partymeister.slidemeister-web.1')
    expect(store.isConnected).toBe(true)
    expect(store.channelName).toBe('partymeister.slidemeister-web.1')
    expect(store.error).toBeNull()
  })

  it('should track disconnection with error', () => {
    const store = useConnectionStore()
    store.setConnected('test-channel')
    store.setDisconnected('Connection lost')
    expect(store.isConnected).toBe(false)
    expect(store.error).toBe('Connection lost')
  })

  it('should clear error on reconnection', () => {
    const store = useConnectionStore()
    store.setDisconnected('Error')
    store.setConnected('test-channel')
    expect(store.error).toBeNull()
  })

  it('should track last event timestamp', () => {
    const store = useConnectionStore()
    const before = Date.now()
    store.recordEvent()
    expect(store.lastEventAt).toBeGreaterThanOrEqual(before)
  })

  it('should have null lastEventAt initially', () => {
    const store = useConnectionStore()
    expect(store.lastEventAt).toBeNull()
  })

  it('should disconnect without error message', () => {
    const store = useConnectionStore()
    store.setConnected('test-channel')
    store.setDisconnected()
    expect(store.isConnected).toBe(false)
    expect(store.error).toBeNull()
  })

  it('should preserve channelName on disconnect', () => {
    const store = useConnectionStore()
    store.setConnected('test-channel')
    store.setDisconnected('Connection lost')
    expect(store.channelName).toBe('test-channel')
  })
})
