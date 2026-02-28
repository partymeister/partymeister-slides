import 'fake-indexeddb/auto'

// Mock HTMLMediaElement methods not available in happy-dom
if (typeof HTMLMediaElement !== 'undefined') {
  Object.defineProperty(HTMLMediaElement.prototype, 'play', {
    value: vi.fn(() => Promise.resolve()),
    writable: true,
  })
  Object.defineProperty(HTMLMediaElement.prototype, 'pause', {
    value: vi.fn(),
    writable: true,
  })
  Object.defineProperty(HTMLMediaElement.prototype, 'load', {
    value: vi.fn(),
    writable: true,
  })
}

// Mock CABLES global
;(globalThis as any).CABLES = {
  patch: {
    setVariable: vi.fn(),
  },
}

// Mock window globals that Blade template normally provides
;(globalThis as any).TOKEN = 'test-token'
;(globalThis as any).BASE_URL = 'http://localhost'
