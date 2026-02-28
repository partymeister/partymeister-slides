import type { SlideElement, SlideDefinitions } from '@common/types/editor'
import { serializeElements } from '@common/composables/useHtmlSerializer'
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
