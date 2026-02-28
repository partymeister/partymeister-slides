import { useStorage } from '@/composables/useStorage'

describe('useStorage', () => {
  let storage: ReturnType<typeof useStorage>

  beforeEach(async () => {
    storage = useStorage()
    await storage.clear()
  })

  it('should save and retrieve a value', async () => {
    await storage.save('testKey', { foo: 'bar' })
    const result = await storage.load<{ foo: string }>('testKey')
    expect(result).toEqual({ foo: 'bar' })
  })

  it('should return null for a missing key', async () => {
    const result = await storage.load('nonexistent')
    expect(result).toBeNull()
  })

  it('should overwrite an existing value', async () => {
    await storage.save('key', 'first')
    await storage.save('key', 'second')
    const result = await storage.load<string>('key')
    expect(result).toBe('second')
  })

  it('should delete a specific key', async () => {
    await storage.save('key', 'value')
    await storage.remove('key')
    const result = await storage.load('key')
    expect(result).toBeNull()
  })

  it('should clear all stored data', async () => {
    await storage.save('key1', 'value1')
    await storage.save('key2', 'value2')
    await storage.clear()
    const r1 = await storage.load('key1')
    const r2 = await storage.load('key2')
    expect(r1).toBeNull()
    expect(r2).toBeNull()
  })

  it('should handle complex objects', async () => {
    const playlist = { id: 1, name: 'Test', items: [{ id: 1 }, { id: 2 }] }
    await storage.save('playlist', playlist)
    const result = await storage.load<typeof playlist>('playlist')
    expect(result).toEqual(playlist)
  })
})
