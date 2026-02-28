import { useMidi } from '@/composables/useMidi'

const mockOutput = {
  name: 'Test MIDI Device',
  id: 'test-device-1',
  sendNoteOn: vi.fn(),
  sendNoteOff: vi.fn(),
}

const mockWebMidi = {
  enable: vi.fn(),
  outputs: [mockOutput] as any[],
}

vi.mock('webmidi', () => ({
  WebMidi: mockWebMidi,
}))

describe('useMidi', () => {
  let midi: ReturnType<typeof useMidi>

  beforeEach(() => {
    vi.clearAllMocks()
    vi.useFakeTimers()
    mockWebMidi.enable.mockResolvedValue(undefined)
    mockWebMidi.outputs = [mockOutput]
    midi = useMidi()
  })

  afterEach(() => {
    vi.useRealTimers()
  })

  it('enable() calls WebMidi.enable() and sets isAvailable to true', async () => {
    const result = await midi.enable()

    expect(mockWebMidi.enable).toHaveBeenCalledWith({ sysex: true })
    expect(midi.isAvailable.value).toBe(true)
    expect(result).toBe(true)
  })

  it('enable() sets isAvailable to false when WebMidi.enable() rejects', async () => {
    mockWebMidi.enable.mockRejectedValueOnce(new Error('MIDI not supported'))

    const result = await midi.enable()

    expect(midi.isAvailable.value).toBe(false)
    expect(result).toBe(false)
  })

  it('playNote(note) sends note on first output with default options', async () => {
    await midi.enable()

    midi.playNote(60)

    expect(mockOutput.sendNoteOn).toHaveBeenCalledWith(60, { channels: 1, rawAttack: 127 })

    vi.advanceTimersByTime(1000)

    expect(mockOutput.sendNoteOff).toHaveBeenCalledWith(60, { channels: 1 })
  })

  it('playNote(0) does nothing (note 0 is "no MIDI")', async () => {
    await midi.enable()

    midi.playNote(0)

    expect(mockOutput.sendNoteOn).not.toHaveBeenCalled()
    expect(mockOutput.sendNoteOff).not.toHaveBeenCalled()
  })

  it('playNote(note) does nothing when no outputs available', async () => {
    mockWebMidi.outputs = []
    await midi.enable()

    midi.playNote(60)

    expect(mockOutput.sendNoteOn).not.toHaveBeenCalled()
  })

  it('playNote(note) does nothing when not enabled', () => {
    midi.playNote(60)

    expect(mockOutput.sendNoteOn).not.toHaveBeenCalled()
  })

  it('sendStopSignal() sends note 103 on channel 1', async () => {
    await midi.enable()

    midi.sendStopSignal()

    expect(mockOutput.sendNoteOn).toHaveBeenCalledWith(103, { channels: 1, rawAttack: 127 })

    vi.advanceTimersByTime(1000)

    expect(mockOutput.sendNoteOff).toHaveBeenCalledWith(103, { channels: 1 })
  })

  it('sendStopSignal() does nothing when no outputs', async () => {
    mockWebMidi.outputs = []
    await midi.enable()

    midi.sendStopSignal()

    expect(mockOutput.sendNoteOn).not.toHaveBeenCalled()
  })

  it('outputName returns name of first output', async () => {
    await midi.enable()

    expect(midi.outputName.value).toBe('Test MIDI Device')
  })

  it('outputName returns null when no outputs', async () => {
    mockWebMidi.outputs = []
    await midi.enable()

    expect(midi.outputName.value).toBeNull()
  })
})
