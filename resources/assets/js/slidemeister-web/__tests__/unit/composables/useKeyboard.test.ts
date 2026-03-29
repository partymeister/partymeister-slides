import { useKeyboard } from '@/composables/useKeyboard'

function createMockDeps() {
  return {
    seekToNext: vi.fn(),
    seekToPrevious: vi.fn(),
    playJingle: vi.fn(),
    playMidiOnly: vi.fn(),
    sendStopSignal: vi.fn(),
    stopJingle: vi.fn(),
    triggerSiegmeister: vi.fn(),
    getCurrentSlideType: vi.fn(() => ''),
    hasPlaylistItems: vi.fn(() => true),
    isPlayNow: vi.fn(() => false),
    setClearPlayNowAfter: vi.fn(),
  }
}

function pressKey(key: string, options: Partial<KeyboardEventInit> = {}) {
  window.dispatchEvent(new KeyboardEvent('keydown', { key, ...options }))
}

describe('useKeyboard', () => {
  let deps: ReturnType<typeof createMockDeps>
  let keyboard: ReturnType<typeof useKeyboard>

  beforeEach(() => {
    deps = createMockDeps()
    // We need to call enable() manually since we're not in a component context
    keyboard = useKeyboard(deps)
    keyboard.enable()
  })

  afterEach(() => {
    keyboard.disable()
  })

  it('should call seekToNext(false) on ArrowRight', () => {
    pressKey('ArrowRight')
    expect(deps.seekToNext).toHaveBeenCalledWith(false)
  })

  it('should call seekToNext(true) on Shift+ArrowRight', () => {
    pressKey('ArrowRight', { shiftKey: true })
    expect(deps.seekToNext).toHaveBeenCalledWith(true)
  })

  it('should call seekToPrevious(false) on ArrowLeft', () => {
    pressKey('ArrowLeft')
    expect(deps.seekToPrevious).toHaveBeenCalledWith(false)
  })

  it('should call seekToPrevious(true) on Shift+ArrowLeft', () => {
    pressKey('ArrowLeft', { shiftKey: true })
    expect(deps.seekToPrevious).toHaveBeenCalledWith(true)
  })

  it('should call playJingle with index for F1-F4', () => {
    pressKey('F1')
    expect(deps.playJingle).toHaveBeenCalledWith(1)
    pressKey('F4')
    expect(deps.playJingle).toHaveBeenCalledWith(4)
  })

  it('should call playMidiOnly with index for F5-F10', () => {
    pressKey('F5')
    expect(deps.playMidiOnly).toHaveBeenCalledWith(5)
    pressKey('F10')
    expect(deps.playMidiOnly).toHaveBeenCalledWith(10)
  })

  it('should stop jingle and send MIDI stop on Escape', () => {
    pressKey('Escape')
    expect(deps.stopJingle).toHaveBeenCalled()
    expect(deps.sendStopSignal).toHaveBeenCalled()
  })

  it('should trigger siegmeister on Space when on siegmeister_bars slide', () => {
    deps.getCurrentSlideType.mockReturnValue('siegmeister_bars')
    pressKey(' ')
    expect(deps.triggerSiegmeister).toHaveBeenCalled()
  })

  it('should NOT trigger siegmeister on Space when on other slide types', () => {
    deps.getCurrentSlideType.mockReturnValue('announce')
    pressKey(' ')
    expect(deps.triggerSiegmeister).not.toHaveBeenCalled()
  })

  it('should toggle debugTier 1 on d key and tier 2 on D key', () => {
    expect(keyboard.debugTier.value).toBe(0)
    pressKey('d')
    expect(keyboard.debugTier.value).toBe(1)
    pressKey('d')
    expect(keyboard.debugTier.value).toBe(0)
    pressKey('D')
    expect(keyboard.debugTier.value).toBe(2)
    pressKey('D')
    expect(keyboard.debugTier.value).toBe(0)
  })

  it('should toggle showKeyboardHelp on ? key', () => {
    expect(keyboard.showKeyboardHelp.value).toBe(false)
    pressKey('?')
    expect(keyboard.showKeyboardHelp.value).toBe(true)
    pressKey('?')
    expect(keyboard.showKeyboardHelp.value).toBe(false)
  })

  it('should set clearPlayNowAfter when ArrowRight during playNow with items', () => {
    deps.isPlayNow.mockReturnValue(true)
    deps.hasPlaylistItems.mockReturnValue(true)
    pressKey('ArrowRight')
    expect(deps.setClearPlayNowAfter).toHaveBeenCalled()
    expect(deps.seekToNext).toHaveBeenCalledWith(false)
  })

  it('should not set clearPlayNowAfter when not in playNow', () => {
    deps.isPlayNow.mockReturnValue(false)
    pressKey('ArrowRight')
    expect(deps.setClearPlayNowAfter).not.toHaveBeenCalled()
  })

  it('should not respond to keys after disable()', () => {
    keyboard.disable()
    pressKey('ArrowRight')
    expect(deps.seekToNext).not.toHaveBeenCalled()
  })
})
