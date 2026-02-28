# Slidemeister Generator — Design Document

**Date:** 2026-02-28
**Scope:** Competition playlist generation (prizegiving/timetable deferred)
**Approach:** Pure frontend replacement — Vue 3 SPA with TypeScript replacement engine

## Architecture

Standalone Vue 3 + TypeScript SPA at `resources/assets/js/slidemeister-generator/`.
Same pattern as `slidemeister-editor` — standalone page, API-driven, no old Blade dependency.

### Flow

1. User clicks "Generate Playlist" in backend → opens `/slidemeister-generator/competition/{id}`
2. Vue app mounts, fetches `GET /api/competitions/{id}/playlist-data`
3. Receives: templates (by type), entries, videos, participants, competition metadata
4. For each slide: loads template definitions → applies `<<placeholder>>` replacements → serializes HTML
5. Shows all slides at 50% scale (480x270) in a scrollable grid
6. User clicks "Save Playlist" → `POST /api/competitions/{id}/playlist` with all slide data
7. Backend `PlaylistService::generateCompetitionPlaylist()` creates Slide + PlaylistItem records

## Shared Code (`slidemeister-common/`)

Extracted from `slidemeister-editor/` — both apps import via `@common/` Vite alias.

```
resources/assets/js/slidemeister-common/
├── types/
│   ├── editor.ts        # SlideElement, SlideDefinitions, ElementCoordinates, etc.
│   └── api.ts           # SlideTemplateResponse, ApiResponse, FontResponse, SaveTemplateData
├── composables/
│   ├── useHtmlSerializer.ts   # Pure functions: serializeElement(), serializeElements()
│   └── useApi.ts              # API request helper + endpoint functions
└── index.ts
```

### useHtmlSerializer Refactor

Current: `useHtmlSerializer(editorStore)` — coupled to Pinia store.
New: Pure functions that take element data directly.

```typescript
export function serializeElement(el: SlideElement): string { ... }
export function serializeElements(elements: Record<string, SlideElement>): string {
  return Object.values(elements)
    .sort((a, b) => b.properties.zIndex - a.properties.zIndex)
    .map(serializeElement)
    .join('\n')
}
```

Editor call site changes from `htmlSerializer.serializeAll()` to `serializeElements(editorStore.elements)`.

## Generator App Structure

```
resources/assets/js/slidemeister-generator/
├── App.vue                    # Main: fetch, orchestrate, preview grid, save
├── main.ts                    # Vite entry point
├── components/
│   └── SlidePreview.vue       # 960x540 container at scale(0.5), v-html injection
├── composables/
│   └── useSlideReplacer.ts    # Core replacement engine
└── types/
    └── generator.ts           # Generator-specific types
```

## Replacement Engine (`useSlideReplacer`)

Ported from old Vue 2 mixins (`renderHelper.js`, `renderCompetition.js`) to pure TypeScript.

### Core Operations

**`replaceContent(element, replacements)`**
- Searches `element.properties.placeholder` for `<<key>>` patterns
- Replaces with corresponding values from replacements object
- Sets result to `element.properties.content`
- If remaining `<<...>>` exist, updates placeholder for chained replacements

**`cloneElement(element, suffix, yOffset)`**
- Deep-clones element, appends suffix to name
- Parses CSS transform matrix string
- Adds yOffset to Y translation component
- Returns new SlideElement

**`stripLeftoverPlaceholders(element)`**
- Removes any remaining `<<...>>` patterns from content

### Render Functions (Competition)

| Function | Replacements | Logic |
|----------|-------------|-------|
| `renderCompetitionSupport(elements, {headline, competitionName})` | `<<headline>>`, `<<body>>` | Simple replacement |
| `renderCompetitionEntry(elements, entry)` | All entry fields as `<<field>>` | Replace all, lowercase `remote_type`, strip leftover |
| `renderCompetitionParticipants(elements, participantsString)` | `<<participants>>` | Replace, strip leftover |

All functions return new element records (immutable — no mutation of inputs).

### Processing Pipeline

```typescript
const slides: GeneratedSlide[] = []

// Support slides
slides.push(generateSlide('comingup', 'comingup', 'Coming up',
  renderCompetitionSupport(clone(templates.coming_up), { headline: 'Coming up', competitionName })))

// Videos (no rendering — just preview images)
videos.forEach((v, i) => slides.push({ key: `video_${i+1}`, type: `video_${i+1}`, ... }))

slides.push(generateSlide('now', 'now', 'Now',
  renderCompetitionSupport(clone(templates.now), { headline: 'Now', competitionName })))

// Entries
entries.forEach((entry, i) => {
  const tpl = i === 0 ? templates.competition_entry_1 : templates.competition
  slides.push(generateSlide(`entry_${entry.id}`, 'entry', `Entry #${i+1}`,
    renderCompetitionEntry(clone(tpl), entry), entry.id))
})

// Participants (if anonymous competition)
if (participants.length > 0) {
  slides.push(generateSlide('participants', 'participants', 'Participants',
    renderCompetitionParticipants(clone(templates.participants), participants.join(', '))))
}

slides.push(generateSlide('end', 'end', 'End',
  renderCompetitionSupport(clone(templates.end), { headline: 'End', competitionName })))
```

## API Endpoints

### GET `/api/competitions/{id}/playlist-data`

Returns all data needed for slide generation.

```json
{
  "competition": {
    "id": 1, "name": "PC Demo",
    "competition_type": { "is_anonymous": false }
  },
  "templates": {
    "coming_up": { "id": 5, "definitions": "{...}" },
    "now": { "id": 6, "definitions": "{...}" },
    "end": { "id": 7, "definitions": "{...}" },
    "competition_entry_1": { "id": 8, "definitions": "{...}" },
    "competition": { "id": 9, "definitions": "{...}" },
    "participants": { "id": 10, "definitions": "{...}" }
  },
  "entries": [
    { "id": 123, "title": "Cool Demo", "author": "GroupName", "remote_type": "", ... }
  ],
  "participants": ["Author1", "Author2"],
  "videos": [
    { "file_id": 45, "preview": "/media/...", "data": {...} }
  ]
}
```

Controller reuses exact same data preparation logic from existing `PlaylistsController@index`.

### POST `/api/competitions/{id}/playlist`

Accepts generated slides, calls `PlaylistService::generateCompetitionPlaylist()`.

```json
{
  "slides": [
    {
      "key": "comingup",
      "type": "comingup",
      "name": "Coming up",
      "definitions": "{...}",
      "cached_html_preview": "<div>...</div>",
      "cached_html_final": "<div>...</div>"
    },
    {
      "key": "entry_123",
      "type": "entry",
      "name": "Entry #1",
      "id": 123,
      "definitions": "{...}",
      "cached_html_preview": "...",
      "cached_html_final": "..."
    }
  ],
  "videos": [
    { "key": "video_1", "file_id": 45, "data": {...} }
  ]
}
```

Store endpoint reshapes JSON to the format `generateCompetitionPlaylist()` expects.

## UI

Three states:
1. **Loading** — spinner while fetching API data
2. **Preview** — scrollable grid of slides at 50% scale (480x270 each), "Save Playlist" button at top, "Back" link
3. **Saved** — success message with link back to backend

**SlidePreview.vue:** 960x540 container with `transform: scale(0.5); transform-origin: top left;` wrapped in a 480x270 overflow-hidden div. Uses `v-html` for slide content. Videos show preview image. Label underneath each slide.

**Font loading:** Blade template includes `@include('partymeister-slides::layouts.partials.slide_fonts')`.

## Backend Changes

### New Controller

`Partymeister\Competitions\Http\Controllers\Api\CompetitionPlaylistController`
- `show(Competition $competition)` — returns playlist-data JSON
- `store(Competition $competition, Request $request)` — accepts JSON, reshapes, calls PlaylistService

### New Routes

API routes in partymeister-competitions:
```php
Route::get('competitions/{competition}/playlist', [CompetitionPlaylistController::class, 'show']);
Route::post('competitions/{competition}/playlist', [CompetitionPlaylistController::class, 'store']);
```

Web route in partymeister-slides:
```php
Route::get('/slidemeister-generator/competition/{competition}', ...);
```

### New Blade Template

`resources/views/slidemeister-generator/index.blade.php` — minimal HTML, font includes, Vite entry, window vars (COMPETITION_ID, TOKEN, BASE_URL).

### No changes to PlaylistService

Store endpoint reshapes JSON input to match existing `$data` format.

## Future Extensibility

The replacement engine is designed to support additional render types:
- `renderTimetable(elements, {headline, rows})` — with element cloning at 40px Y-offset
- `renderPrizegivingSupport/Slide/Winners(elements, ...)` — with 50px Y-offset + bar coordinates
- `renderEventSupport(elements, ...)` — simple replacement

These will be added when we tackle prizegiving and timetable playlist generation.
