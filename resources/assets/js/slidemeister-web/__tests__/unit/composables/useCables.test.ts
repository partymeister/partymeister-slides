import { useCables } from '@/composables/useCables'

function setupCablesMock() {
  ;(globalThis as any).CABLES = {
    patch: {
      setVariable: vi.fn(),
    },
  }
}

describe('useCables', () => {
  beforeEach(() => {
    setupCablesMock()
  })

  afterEach(() => {
    delete (globalThis as any).CABLES
  })

  describe('isReady', () => {
    it('should be true when CABLES is available', () => {
      const { isReady } = useCables()
      expect(isReady.value).toBe(true)
    })

    it('should be false when CABLES is not available', () => {
      delete (globalThis as any).CABLES
      const { isReady } = useCables()
      expect(isReady.value).toBe(false)
    })
  })

  describe('setScene', () => {
    it('should call setVariable with correct CablesSlideData', () => {
      const { setScene } = useCables()
      const before = Date.now()
      setScene(2, true)
      const after = Date.now()

      const mock = (globalThis as any).CABLES.patch.setVariable
      expect(mock).toHaveBeenCalledWith('currentSlide', expect.objectContaining({
        scene: 2,
        transition: true,
      }))
      const data = mock.mock.calls[0][1]
      expect(data.time).toBeGreaterThanOrEqual(before)
      expect(data.time).toBeLessThanOrEqual(after)
    })
  })

  describe('setSlideType', () => {
    it('should call setVariable with SLIDETYPE', () => {
      const { setSlideType } = useCables()
      setSlideType('compo')

      expect((globalThis as any).CABLES.patch.setVariable).toHaveBeenCalledWith('SLIDETYPE', 'compo')
    })
  })

  describe('setCompetitionName', () => {
    it('should set eventOrCompoName for short names (<= 8 chars)', () => {
      const { setCompetitionName } = useCables()
      setCompetitionName('GFX')

      const mock = (globalThis as any).CABLES.patch.setVariable
      expect(mock).toHaveBeenCalledWith('eventOrCompoNameLong', '')
      expect(mock).toHaveBeenCalledWith('eventOrCompoName', 'GFX')
    })

    it('should set eventOrCompoNameLong for long names (> 8 chars)', () => {
      const { setCompetitionName } = useCables()
      setCompetitionName('Executable Graphics')

      const mock = (globalThis as any).CABLES.patch.setVariable
      expect(mock).toHaveBeenCalledWith('eventOrCompoName', '')
      expect(mock).toHaveBeenCalledWith('eventOrCompoNameLong', 'Executable Graphics')
    })

    it('should set eventOrCompoName for exactly 8 char names', () => {
      const { setCompetitionName } = useCables()
      setCompetitionName('12345678')

      const mock = (globalThis as any).CABLES.patch.setVariable
      expect(mock).toHaveBeenCalledWith('eventOrCompoNameLong', '')
      expect(mock).toHaveBeenCalledWith('eventOrCompoName', '12345678')
    })
  })

  describe('setSlideTypeString', () => {
    it('should set the label text via slideTypeString', () => {
      const { setSlideTypeString } = useCables()
      setSlideTypeString('COMING UP')

      expect((globalThis as any).CABLES.patch.setVariable).toHaveBeenCalledWith('slideTypeString', 'COMING UP')
    })
  })

  describe('setEntryType', () => {
    it('should set currentSlide with scene 3 and entryType', () => {
      const { setEntryType } = useCables()
      setEntryType('satellite')

      const mock = (globalThis as any).CABLES.patch.setVariable
      expect(mock).toHaveBeenCalledWith('currentSlide', expect.objectContaining({
        scene: 3,
        transition: true,
        entryType: 'satellite',
      }))
    })
  })

  describe('updateForSlideType', () => {
    beforeEach(() => {
      ;(globalThis as any).CABLES.patch.setVariable.mockClear()
    })

    it('should set scene 0 for empty slide_type', () => {
      const { updateForSlideType } = useCables()
      updateForSlideType('', true)

      const mock = (globalThis as any).CABLES.patch.setVariable
      expect(mock).toHaveBeenCalledWith('SLIDETYPE', '')
      expect(mock).toHaveBeenCalledWith('currentSlide', expect.objectContaining({
        scene: 0,
        transition: true,
      }))
    })

    it.each([
      'siegmeister_winners',
      'siegmeister_bars',
      'comments',
      'announce',
      'announce_important',
      'timetable',
    ] as const)('should set scene 1 for %s', (slideType) => {
      const { updateForSlideType } = useCables()
      updateForSlideType(slideType, true)

      const mock = (globalThis as any).CABLES.patch.setVariable
      expect(mock).toHaveBeenCalledWith('SLIDETYPE', slideType)
      expect(mock).toHaveBeenCalledWith('currentSlide', expect.objectContaining({
        scene: 1,
        transition: true,
      }))
    })

    it('should set scene 2 and slideTypeString "COMING UP" for comingup', () => {
      const { updateForSlideType } = useCables()
      updateForSlideType('comingup', true)

      const mock = (globalThis as any).CABLES.patch.setVariable
      expect(mock).toHaveBeenCalledWith('currentSlide', expect.objectContaining({
        scene: 2,
        transition: true,
      }))
      expect(mock).toHaveBeenCalledWith('slideTypeString', 'COMING UP')
    })

    it('should set scene 2 and slideTypeString "NOW" for now', () => {
      const { updateForSlideType } = useCables()
      updateForSlideType('now', false)

      const mock = (globalThis as any).CABLES.patch.setVariable
      expect(mock).toHaveBeenCalledWith('currentSlide', expect.objectContaining({
        scene: 2,
        transition: false,
      }))
      expect(mock).toHaveBeenCalledWith('slideTypeString', 'NOW')
    })

    it('should set scene 2 and slideTypeString "END" for end', () => {
      const { updateForSlideType } = useCables()
      updateForSlideType('end', true)

      const mock = (globalThis as any).CABLES.patch.setVariable
      expect(mock).toHaveBeenCalledWith('currentSlide', expect.objectContaining({
        scene: 2,
        transition: true,
      }))
      expect(mock).toHaveBeenCalledWith('slideTypeString', 'END')
    })

    it('should set scene 3 with entryType for compo', () => {
      const { updateForSlideType } = useCables()
      updateForSlideType('compo', true, null, 'satellite')

      const mock = (globalThis as any).CABLES.patch.setVariable
      expect(mock).toHaveBeenCalledWith('currentSlide', expect.objectContaining({
        scene: 3,
        transition: true,
        entryType: 'satellite',
      }))
    })

    it('should default entryType to "party" for compo when not provided', () => {
      const { updateForSlideType } = useCables()
      updateForSlideType('compo', true)

      const mock = (globalThis as any).CABLES.patch.setVariable
      expect(mock).toHaveBeenCalledWith('currentSlide', expect.objectContaining({
        scene: 3,
        entryType: 'party',
      }))
    })

    it('should skip CABLES update when same background and no competition change (deduplication)', () => {
      const { updateForSlideType } = useCables()

      // First call sets the background
      updateForSlideType('announce', true)
      ;(globalThis as any).CABLES.patch.setVariable.mockClear()

      // Second call with same type should be skipped
      updateForSlideType('announce', true)

      expect((globalThis as any).CABLES.patch.setVariable).not.toHaveBeenCalled()
    })

    it('should always update for compo type (no deduplication)', () => {
      const { updateForSlideType } = useCables()

      updateForSlideType('compo', true, null, 'party')
      ;(globalThis as any).CABLES.patch.setVariable.mockClear()

      updateForSlideType('compo', true, null, 'remote')

      expect((globalThis as any).CABLES.patch.setVariable).toHaveBeenCalled()
      expect((globalThis as any).CABLES.patch.setVariable).toHaveBeenCalledWith(
        'currentSlide',
        expect.objectContaining({ scene: 3, entryType: 'remote' }),
      )
    })

    it('should extract competition name from HTML and set it for scene 2 types', () => {
      const { updateForSlideType } = useCables()
      const html = '<div><span data-partymeister-slides-prettyname="competition">Demo Compo</span></div>'

      updateForSlideType('comingup', true, html)

      const mock = (globalThis as any).CABLES.patch.setVariable
      expect(mock).toHaveBeenCalledWith('slideTypeString', 'COMING UP')
      // "Demo Compo" is > 8 chars, so it goes to eventOrCompoNameLong
      expect(mock).toHaveBeenCalledWith('eventOrCompoName', '')
      expect(mock).toHaveBeenCalledWith('eventOrCompoNameLong', 'Demo Compo')
    })
  })

  describe('extractCompetitionName', () => {
    it('should extract name from data attribute in HTML', () => {
      const { extractCompetitionName } = useCables()
      const html = '<div><span data-partymeister-slides-prettyname="competition">Wild</span></div>'
      expect(extractCompetitionName(html)).toBe('Wild')
    })

    it('should return null when no matching element exists', () => {
      const { extractCompetitionName } = useCables()
      const html = '<div><span>No competition here</span></div>'
      expect(extractCompetitionName(html)).toBeNull()
    })

    it('should return null for empty HTML', () => {
      const { extractCompetitionName } = useCables()
      expect(extractCompetitionName('')).toBeNull()
    })
  })
})
