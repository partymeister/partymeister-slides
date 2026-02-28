import { ref } from 'vue'
import { useJingles } from '@/composables/useJingles'
import type { JingleConfig } from '@/types/config'

describe('useJingles', () => {
  const mockAudio = {
    src: '',
    currentTime: 0,
    play: vi.fn(),
    pause: vi.fn(),
  } as unknown as HTMLAudioElement

  const getMidiNote = vi.fn()
  const midiPlayNote = vi.fn()

  let jingles: ReturnType<typeof useJingles>

  const jingleConfig: JingleConfig = {
    jingle_1: 'https://example.com/jingle1.mp3',
    jingle_2: 'https://example.com/jingle2.mp3',
    jingle_3: null,
    jingle_4: 'https://example.com/jingle4.mp3',
  }

  beforeEach(() => {
    vi.clearAllMocks()
    mockAudio.src = ''
    mockAudio.currentTime = 0
    const audioRef = ref<HTMLAudioElement | null>(mockAudio)
    jingles = useJingles(audioRef, getMidiNote, midiPlayNote)
    jingles.loadJingles(jingleConfig)
  })

  it('loadJingles populates jingleUrls from config', () => {
    expect(jingles.jingleUrls.value[1]).toBe('https://example.com/jingle1.mp3')
    expect(jingles.jingleUrls.value[2]).toBe('https://example.com/jingle2.mp3')
    expect(jingles.jingleUrls.value[3]).toBeNull()
    expect(jingles.jingleUrls.value[4]).toBe('https://example.com/jingle4.mp3')
  })

  it('play sets audio src, resets currentTime, and calls play()', () => {
    getMidiNote.mockReturnValue(0)

    jingles.play(1)

    expect(mockAudio.src).toBe('https://example.com/jingle1.mp3')
    expect(mockAudio.currentTime).toBe(0)
    expect(mockAudio.play).toHaveBeenCalled()
  })

  it('play sends MIDI note when getMidiNote returns > 0', () => {
    getMidiNote.mockReturnValue(60)

    jingles.play(2)

    expect(getMidiNote).toHaveBeenCalledWith(2)
    expect(midiPlayNote).toHaveBeenCalledWith(60)
  })

  it('play does not send MIDI when getMidiNote returns 0', () => {
    getMidiNote.mockReturnValue(0)

    jingles.play(1)

    expect(midiPlayNote).not.toHaveBeenCalled()
  })

  it('play does nothing when jingle URL is null', () => {
    getMidiNote.mockReturnValue(0)

    jingles.play(3)

    expect(mockAudio.play).not.toHaveBeenCalled()
  })

  it('play does nothing when jingle URL is not loaded (unknown index)', () => {
    getMidiNote.mockReturnValue(0)

    jingles.play(99)

    expect(mockAudio.play).not.toHaveBeenCalled()
  })

  it('play does nothing when audioRef is null', () => {
    const nullAudioRef = ref<HTMLAudioElement | null>(null)
    const nullJingles = useJingles(nullAudioRef, getMidiNote, midiPlayNote)
    nullJingles.loadJingles(jingleConfig)
    getMidiNote.mockReturnValue(0)

    nullJingles.play(1)

    expect(mockAudio.play).not.toHaveBeenCalled()
  })

  it('stop pauses audio and resets currentTime', () => {
    jingles.stop()

    expect(mockAudio.pause).toHaveBeenCalled()
    expect(mockAudio.currentTime).toBe(0)
  })

  it('stop does nothing when audioRef is null', () => {
    const nullAudioRef = ref<HTMLAudioElement | null>(null)
    const nullJingles = useJingles(nullAudioRef, getMidiNote, midiPlayNote)

    // Should not throw
    nullJingles.stop()

    expect(mockAudio.pause).not.toHaveBeenCalled()
  })
})
