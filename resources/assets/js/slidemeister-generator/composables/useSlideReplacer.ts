import type { SlideElement, SlideDefinitions } from '@common/types/editor'
import { serializeElement, serializeElements } from '@common/composables/useHtmlSerializer'
import type { GeneratedSlide, CompetitionData, EntryData } from '@/types/generator'

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
  }
  return els
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
