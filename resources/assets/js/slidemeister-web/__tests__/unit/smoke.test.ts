describe('test infrastructure', () => {
  it('should run a basic test', () => {
    expect(1 + 1).toBe(2)
  })

  it('should have fake-indexeddb available', async () => {
    const { openDB } = await import('idb')
    const db = await openDB('test-smoke', 1, {
      upgrade(db) {
        db.createObjectStore('store')
      },
    })
    await db.put('store', 'value', 'key')
    const result = await db.get('store', 'key')
    expect(result).toBe('value')
    db.close()
  })

  it('should have CABLES mock available', () => {
    expect((globalThis as any).CABLES.patch.setVariable).toBeDefined()
  })
})
