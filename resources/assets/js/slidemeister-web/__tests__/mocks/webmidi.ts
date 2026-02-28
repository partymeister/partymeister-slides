export function createMockMidiOutput() {
  return {
    id: 'mock-output-1',
    name: 'Mock MIDI Output',
    sendNoteOn: vi.fn(),
    sendNoteOff: vi.fn(),
  }
}

export function createMockWebMidi(outputs: any[] = [createMockMidiOutput()]) {
  return {
    enable: vi.fn(() => Promise.resolve()),
    disable: vi.fn(),
    outputs,
    enabled: true,
  }
}
