import type { SlideElement, SlideDefinitions } from '@common/types/editor'
import { serializeElement, serializeElements } from '@common/composables/useHtmlSerializer'
import type {
  GeneratedSlide, CompetitionData, EntryData,
  TimetableRow, TimetableData,
  PrizegivingRow, PrizegivingCompetition, PrizegivingData,
  EventData,
} from '@/types/generator'

function deepClone<T>(obj: T): T {
  return JSON.parse(JSON.stringify(obj))
}

function replaceContent(
  element: SlideElement,
  name: string | string[],
  value: string | string[]
): void {
  let content = element.properties.placeholder

  if (Array.isArray(name) && Array.isArray(value)) {
    for (let i = 0; i < name.length; i++) {
      content = content.replace('<<' + name[i] + '>>', value[i] ?? '')
    }
  } else if (typeof name === 'string' && typeof value === 'string') {
    content = content.replace('<<' + name + '>>', value)
  }

  if (content !== element.properties.placeholder) {
    element.properties.content = content
    if (element.properties.content.includes('<<')) {
      element.properties.placeholder = content
    }
  }
}

function replaceContentGlobal(
  elements: Record<string, SlideElement>,
  name: string,
  value: string
): void {
  for (const element of Object.values(elements)) {
    replaceContent(element, name, value)
  }
}

function stripLeftoverPlaceholders(element: SlideElement): void {
  element.properties.content = element.properties.content.replace(/<<.+?>>/g, '')
}

function renderCompetitionSupport(
  elements: Record<string, SlideElement>,
  headline: string,
  competitionName: string
): Record<string, SlideElement> {
  const els = deepClone(elements)
  for (const element of Object.values(els)) {
    replaceContent(element, 'headline', headline)
    replaceContent(element, 'body', competitionName)
    sanitizeContent(element)
  }
  return els
}

function sanitizeContent(element: SlideElement): void {
  // Strip \r characters — <br /> handles HTML line breaks, raw \r causes MySQL JSON errors
  element.properties.content = element.properties.content.replace(/\r/g, '')
}

function cloneElement(
  elements: Record<string, SlideElement>,
  element: SlideElement,
  suffix: string | number,
  yOffset: number
): SlideElement {
  const cloned = deepClone(element)
  cloned.name = element.name + '_' + suffix

  const transform = cloned.properties.coordinates.transform
  // Extract matrix(...) and translate(Xpx, Ypx) parts
  const matrixMatch = transform.match(/matrix\(([^)]+)\)/)
  const translateMatch = transform.match(/translate\(([^)]+)\)/)

  let tx = 0, ty = 0
  if (translateMatch) {
    const parts = translateMatch[1].replace(/px/g, '').split(',').map((s: string) => parseFloat(s.trim()))
    tx = parts[0] || 0
    ty = parts[1] || 0
  }

  ty += yOffset

  const matrixPart = matrixMatch ? matrixMatch[0] : 'matrix(1, 0, 0, 1, 0, 0)'
  cloned.properties.coordinates.transform = `${matrixPart} translate(${tx}px, ${ty}px)`

  elements[cloned.name] = cloned
  return cloned
}

function replaceColor(element: SlideElement, color: string): void {
  element.properties.color = color
}

function findElementByPrettyname(
  elements: Record<string, SlideElement>,
  prettyname: string
): SlideElement | undefined {
  return Object.values(elements).find(el => el.properties.prettyname === prettyname)
}

function renderTimetable(
  elements: Record<string, SlideElement>,
  headline: string,
  rows: TimetableRow[]
): Record<string, SlideElement> {
  const els = deepClone(elements)
  replaceContentGlobal(els, 'headline', headline)

  const baseTime = findElementByPrettyname(els, 'timetable_time')
  const baseType = findElementByPrettyname(els, 'timetable_event_type')
  const baseName = findElementByPrettyname(els, 'timetable_event_name')

  if (!baseTime || !baseType || !baseName) return els

  let previousTime: string | null = null
  rows.forEach((row, i) => {
    let timeEl: SlideElement, typeEl: SlideElement, nameEl: SlideElement
    if (i === 0) {
      timeEl = baseTime
      typeEl = baseType
      nameEl = baseName
    } else {
      timeEl = cloneElement(els, baseTime, i, 40 * i)
      typeEl = cloneElement(els, baseType, i, 40 * i)
      nameEl = cloneElement(els, baseName, i, 40 * i)
    }

    replaceColor(typeEl, row.color)

    if (row.time === previousTime) {
      replaceContent(timeEl, 'time', '')
    } else {
      replaceContent(timeEl, 'time', row.time)
    }

    replaceContent(typeEl, 'type', row.type)
    replaceContent(nameEl, 'name', row.name)

    previousTime = row.time
  })

  for (const el of Object.values(els)) {
    sanitizeContent(el)
  }

  return els
}

function renderPrizegivingSlideOrWinners(
  elements: Record<string, SlideElement>,
  headline: string,
  rows: PrizegivingRow[],
  includeRank: boolean
): Record<string, SlideElement> {
  const els = deepClone(elements)
  replaceContentGlobal(els, 'headline', headline)

  const baseEntry = findElementByPrettyname(els, 'entry')
  const baseRemoteType = findElementByPrettyname(els, 'remote_type')
  const baseRank = findElementByPrettyname(els, 'rank')

  if (!baseEntry || !baseRemoteType || !baseRank) return els

  rows.forEach((row, i) => {
    let entryEl: SlideElement, remoteTypeEl: SlideElement, rankEl: SlideElement
    if (i === 0) {
      entryEl = baseEntry
      remoteTypeEl = baseRemoteType
      rankEl = baseRank
    } else {
      entryEl = cloneElement(els, baseEntry, i, 50 * i)
      remoteTypeEl = cloneElement(els, baseRemoteType, i, 50 * i)
      rankEl = cloneElement(els, baseRank, i, 50 * i)
    }

    replaceContent(entryEl, ['title', 'author'], [row.title, row.author])
    replaceContent(remoteTypeEl, 'remote_type', row.remote_type)

    if (includeRank) {
      replaceContent(rankEl, 'rank', '#' + row.rank)
    } else {
      replaceContent(rankEl, 'rank', '')
      // Calculate bar coordinates for bars slide
      const coords = calculateBarCoordinates(entryEl, row.points, row.max_points)
      entryEl.properties.prizegivingbarCoordinates = coords
    }
  })

  for (const el of Object.values(els)) {
    stripLeftoverPlaceholders(el)
    sanitizeContent(el)
  }

  return els
}

interface PrizegivingBarCoordinates {
  x1: number
  x2: number
  y1: number
  y2: number
}

function calculateBarCoordinates(
  element: SlideElement,
  points: number,
  maxPoints: number
): PrizegivingBarCoordinates {
  const transform = element.properties.coordinates.transform
  const matrixMatch = transform.match(/matrix\(([^)]+)\)/)
  const translateMatch = transform.match(/translate\(([^)]+)\)/)

  let mx = 0, my = 0
  if (matrixMatch) {
    const vals = matrixMatch[1].split(',').map((s: string) => parseFloat(s.trim()))
    mx = vals[4] || 0
    my = vals[5] || 0
  }

  let tx = 0, ty = 0
  if (translateMatch) {
    const parts = translateMatch[1].replace(/px/g, '').split(',').map((s: string) => parseFloat(s.trim()))
    tx = parts[0] || 0
    ty = parts[1] || 0
  }

  const totalX = mx + tx
  const totalY = my + ty
  const width = element.properties.coordinates.width
  const height = element.properties.coordinates.height

  const barWidth = maxPoints === 0 ? 0 : (points / maxPoints) * width

  return {
    x1: Number((totalX / 960).toFixed(10)),
    x2: Number(((totalX + barWidth) / 960).toFixed(10)),
    y1: Number((totalY / 540).toFixed(10)),
    y2: Number(((totalY + height) / 540).toFixed(10)),
  }
}

function renderCompetitionEntry(
  elements: Record<string, SlideElement>,
  entry: EntryData
): Record<string, SlideElement> {
  const els = deepClone(elements)
  for (const element of Object.values(els)) {
    for (const [property, value] of Object.entries(entry)) {
      let strValue = String(value ?? '')
      if (property === 'remote_type') {
        strValue = strValue.toLowerCase()
      }
      replaceContent(element, property, strValue)
    }
    stripLeftoverPlaceholders(element)
    sanitizeContent(element)
  }
  return els
}

function renderCompetitionParticipants(
  elements: Record<string, SlideElement>,
  participantsString: string
): Record<string, SlideElement> {
  const els = deepClone(elements)
  for (const element of Object.values(els)) {
    replaceContent(element, 'participants', participantsString)
    stripLeftoverPlaceholders(element)
    sanitizeContent(element)
  }
  return els
}

function parseTemplateDefinitions(definitionsJson: string): Record<string, SlideElement> {
  const defs: SlideDefinitions = JSON.parse(definitionsJson)
  return defs.elements
}

const MIN_FONT = 5

/**
 * Measure text in an offscreen DOM container and shrink font size until it fits.
 * Mutates element.properties.calculatedFontSize in place.
 */
function resizeElementText(element: SlideElement, container: HTMLDivElement): void {
  const p = element.properties

  // Skip elements with no text content or image-only elements
  if (!p.content || p.content.trim() === '') return

  // Create a temporary element matching the serialized structure
  const outer = document.createElement('div')
  outer.style.cssText = [
    `width: ${p.coordinates.width}px`,
    `height: ${p.coordinates.height}px`,
    'display: flex',
    `align-items: ${p.verticalAlign}`,
    'position: absolute',
    'overflow: hidden',
  ].join('; ')

  const inner = document.createElement('div')
  inner.style.cssText = [
    `font-family: ${p.fontFamily}`,
    `font-size: ${p.fontSize}px`,
    `font-kerning: ${p.fontKerning}`,
    `font-weight: ${p.fontWeight}`,
    `font-stretch: ${p.fontStretch}%`,
    `font-style: ${p.fontStyle}`,
    `letter-spacing: ${p.letterSpacing}`,
    `line-height: ${p.lineHeight}`,
    `text-transform: ${p.textTransform}`,
    'width: 100%',
  ].join('; ')
  inner.innerHTML = p.content

  outer.appendChild(inner)
  container.appendChild(outer)

  // Shrink until text fits
  let size = p.fontSize
  while (size > MIN_FONT && inner.scrollHeight > p.coordinates.height) {
    size--
    inner.style.fontSize = size + 'px'
  }

  // Safety margin
  if (size > MIN_FONT && (p.coordinates.height - inner.scrollHeight) < 2) {
    size--
  }

  p.calculatedFontSize = size + 'px'

  container.removeChild(outer)
}

/**
 * Resize text in all slides to fit their containers, then serialize HTML.
 * Creates an offscreen 960x540 measurement container, measures each element,
 * updates calculatedFontSize, and re-serializes.
 */
export function resizeTextAndSerialize(slides: GeneratedSlide[]): void {
  // Create offscreen measurement container
  const measureDiv = document.createElement('div')
  measureDiv.style.cssText = [
    'position: absolute',
    'left: -9999px',
    'top: -9999px',
    'width: 960px',
    'height: 540px',
    'overflow: hidden',
  ].join('; ')
  document.body.appendChild(measureDiv)

  for (const slide of slides) {
    // Skip video slides
    if (Object.keys(slide.elements).length === 0) continue

    for (const element of Object.values(slide.elements)) {
      resizeElementText(element, measureDiv)
    }

    // Re-serialize with updated calculatedFontSize
    slide.html = serializeElements(slide.elements)
  }

  document.body.removeChild(measureDiv)
}

function generateSlide(
  key: string,
  type: string,
  name: string,
  elements: Record<string, SlideElement>,
  id?: number
): GeneratedSlide {
  return {
    key,
    type,
    name,
    elements,
    html: serializeElements(elements),
    ...(id !== undefined ? { id } : {}),
  }
}

function renderEventSupport(
  elements: Record<string, SlideElement>,
  headline: string,
  eventName: string
): Record<string, SlideElement> {
  const els = deepClone(elements)
  for (const element of Object.values(els)) {
    replaceContent(element, 'headline', headline)
    replaceContent(element, 'body', eventName)
    sanitizeContent(element)
  }
  return els
}

export function generateEventPlaylist(data: EventData): GeneratedSlide[] {
  const slides: GeneratedSlide[] = []
  const eventName = data.event.name

  // Coming up
  if (data.templates.coming_up) {
    const els = parseTemplateDefinitions(data.templates.coming_up.definitions)
    slides.push(generateSlide(
      'comingup', 'comingup', 'Coming up',
      renderEventSupport(els, 'Coming up', eventName)
    ))
  }

  // Now
  if (data.templates.now) {
    const els = parseTemplateDefinitions(data.templates.now.definitions)
    slides.push(generateSlide(
      'now', 'now', 'Now',
      renderEventSupport(els, 'Now', eventName)
    ))
  }

  // Default (empty)
  if (data.templates.default) {
    const els = parseTemplateDefinitions(data.templates.default.definitions)
    slides.push(generateSlide(
      'default', 'default', 'Default',
      renderEventSupport(els, '', eventName)
    ))
  }

  // End
  if (data.templates.end) {
    const els = parseTemplateDefinitions(data.templates.end.definitions)
    slides.push(generateSlide(
      'end', 'end', 'End',
      renderEventSupport(els, 'End', eventName)
    ))
  }

  return slides
}

export function generateCompetitionPlaylist(data: CompetitionData): GeneratedSlide[] {
  const slides: GeneratedSlide[] = []
  const compName = data.competition.name

  // Coming up
  const comingUpElements = parseTemplateDefinitions(data.templates.coming_up.definitions)
  slides.push(generateSlide(
    'comingup', 'comingup', 'Coming up',
    renderCompetitionSupport(comingUpElements, 'Coming up', compName)
  ))

  // Videos (no replacement — just metadata)
  data.videos.forEach((video, i) => {
    slides.push({
      key: `video_${i + 1}`,
      type: `video_${i + 1}`,
      name: `Video ${i + 1}`,
      elements: {},
      html: '',
    })
  })

  // Now
  const nowElements = parseTemplateDefinitions(data.templates.now.definitions)
  slides.push(generateSlide(
    'now', 'now', 'Now',
    renderCompetitionSupport(nowElements, 'Now', compName)
  ))

  // Entries
  data.entries.forEach((entry, i) => {
    const templateKey = i === 0 ? 'competition_entry_1' : 'competition'
    const elements = parseTemplateDefinitions(data.templates[templateKey].definitions)
    slides.push(generateSlide(
      `entry_${entry.id}`, 'entry', `Entry #${i + 1}`,
      renderCompetitionEntry(elements, entry),
      entry.id
    ))
  })

  // Participants (only for anonymous competitions)
  if (data.participants.length > 0) {
    const participantsElements = parseTemplateDefinitions(data.templates.participants.definitions)
    slides.push(generateSlide(
      'participants', 'participants', 'Participants',
      renderCompetitionParticipants(participantsElements, data.participants.join(', '))
    ))
  }

  // End
  const endElements = parseTemplateDefinitions(data.templates.end.definitions)
  slides.push(generateSlide(
    'end', 'end', 'End',
    renderCompetitionSupport(endElements, 'End', compName)
  ))

  return slides
}

export function generateTimetablePlaylist(data: TimetableData): GeneratedSlide[] {
  const slides: GeneratedSlide[] = []

  for (const [dayName, chunks] of Object.entries(data.days)) {
    chunks.forEach((chunk, chunkIndex) => {
      const elements = parseTemplateDefinitions(data.template.definitions)
      const rendered = renderTimetable(elements, dayName.toUpperCase(), chunk)
      slides.push(generateSlide(
        `${dayName}-${chunkIndex}`, 'timetable', `${dayName} (${chunkIndex + 1})`,
        rendered
      ))
    })
  }

  return slides
}

function generateSlideWithMeta(
  key: string,
  type: string,
  name: string,
  elements: Record<string, SlideElement>,
  meta?: string
): GeneratedSlide & { meta?: string } {
  const slide = generateSlide(key, type, name, elements)
  if (meta) {
    ;(slide as GeneratedSlide & { meta?: string }).meta = meta
  }
  return slide as GeneratedSlide & { meta?: string }
}

function collectBarCoordinates(elements: Record<string, SlideElement>): string {
  const coords: PrizegivingBarCoordinates[] = []
  for (const el of Object.values(elements)) {
    if (el.properties.prizegivingbarCoordinates) {
      coords.push(el.properties.prizegivingbarCoordinates)
    }
  }
  return JSON.stringify(coords)
}

export function generatePrizegivingPlaylist(data: PrizegivingData): GeneratedSlide[] {
  const slides: GeneratedSlide[] = []
  const t = data.templates

  // Global "Coming up" slide
  const comingUpEls = parseTemplateDefinitions(t.coming_up.definitions)
  slides.push(generateSlide(
    'comingup', 'comingup', 'Prizegiving: Coming up',
    renderCompetitionSupport(comingUpEls, 'Coming up', 'Prizegiving')
  ))

  // Global "Now" slide
  const nowEls = parseTemplateDefinitions(t.now.definitions)
  slides.push(generateSlide(
    'now', 'now', 'Prizegiving: Now',
    renderCompetitionSupport(nowEls, 'Now', 'Prizegiving')
  ))

  // Per-competition slides
  for (const [key, competition] of Object.entries(data.results)) {
    // "Now" slide for this competition
    const compNowEls = parseTemplateDefinitions(t.coming_up.definitions)
    slides.push(generateSlide(
      `${key}_now`, 'now', `Competition: ${key} Now`,
      renderCompetitionSupport(compNowEls, 'Now', competition.name)
    ))

    // Optional comments slide
    if (competition.has_comment && data.comments[key] && data.comments[key] !== '') {
      const commentsEls = parseTemplateDefinitions(t.comments.definitions)
      slides.push(generateSlide(
        `${key}_comments`, 'comments', `Competition: ${key} Comments`,
        renderCompetitionSupport(commentsEls, competition.name, data.comments[key])
      ))
    }

    // Bars slide
    const barsEls = parseTemplateDefinitions(t.prizegiving.definitions)
    const barsRendered = renderPrizegivingSlideOrWinners(barsEls, competition.name, competition.entries, false)
    const barsMeta = collectBarCoordinates(barsRendered)
    slides.push(generateSlideWithMeta(
      `${key}_slide`, 'siegmeister_bars', `Competition: ${key} Bars`,
      barsRendered, barsMeta
    ))

    // Winners slide
    const winnersEls = parseTemplateDefinitions(t.prizegiving.definitions)
    const winnersRendered = renderPrizegivingSlideOrWinners(winnersEls, competition.name, competition.entries, true)
    slides.push(generateSlide(
      `${key}_winners`, 'siegmeister_winners', `Competition: ${key} Winners`,
      winnersRendered
    ))
  }

  // Special votes (crowd favourite)
  if (data.specialVotes.length > 0) {
    const specialNowEls = parseTemplateDefinitions(t.coming_up.definitions)
    slides.push(generateSlide(
      'special_now', 'now', 'Special: Now',
      renderCompetitionSupport(specialNowEls, 'Now', 'Crowd favourite')
    ))

    const specialBarsEls = parseTemplateDefinitions(t.prizegiving.definitions)
    const specialBarsRendered = renderPrizegivingSlideOrWinners(specialBarsEls, 'Crowd favourite', data.specialVotes, false)
    const specialBarsMeta = collectBarCoordinates(specialBarsRendered)
    slides.push(generateSlideWithMeta(
      'special_slide', 'siegmeister_bars', 'Special: Bars',
      specialBarsRendered, specialBarsMeta
    ))

    const specialWinnersEls = parseTemplateDefinitions(t.prizegiving.definitions)
    const specialWinnersRendered = renderPrizegivingSlideOrWinners(specialWinnersEls, 'Crowd favourite', data.specialVotes, true)
    slides.push(generateSlide(
      'special_winners', 'siegmeister_winners', 'Special: Winners',
      specialWinnersRendered
    ))
  }

  // End slide
  const endEls = parseTemplateDefinitions(t.end_of_pg.definitions)
  slides.push(generateSlide(
    'end', 'end', 'Prizegiving: End',
    renderCompetitionSupport(endEls, 'End', 'Prizegiving')
  ))

  return slides
}
