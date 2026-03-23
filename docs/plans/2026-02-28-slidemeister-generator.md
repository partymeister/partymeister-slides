# Slidemeister Generator Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Build a Vue 3 SPA that generates competition playlist slides by fetching templates + entry data from a new API, performing `<<placeholder>>` replacement in TypeScript, previewing at 50% scale, and saving via API.

**Architecture:** Standalone Vue 3 + TypeScript app at `slidemeister-generator/`. Shares types, `useApi`, and `useHtmlSerializer` with `slidemeister-editor` via a new `slidemeister-common/` directory. Backend provides two new API endpoints: GET for data, POST for saving. The existing `PlaylistService::generateCompetitionPlaylist()` is reused underneath.

**Tech Stack:** Vue 3.5+, TypeScript strict, Vite (port 5175), no Pinia needed (simple reactive state)

**Design doc:** `docs/plans/2026-02-28-slidemeister-generator-design.md`

---

### Task 1: Extract shared code to `slidemeister-common/`

Move types and composables that both editor and generator need into a shared directory.

**Files:**
- Create: `resources/assets/js/slidemeister-common/types/editor.ts`
- Create: `resources/assets/js/slidemeister-common/types/api.ts`
- Create: `resources/assets/js/slidemeister-common/composables/useApi.ts`
- Create: `resources/assets/js/slidemeister-common/composables/useHtmlSerializer.ts`
- Modify: `resources/assets/js/slidemeister-editor/vite.config.ts` (add `@common` alias)
- Modify: all editor files that import from `@/types/editor`, `@/types/api`, `@/composables/useApi`, `@/composables/useHtmlSerializer`

**Step 1: Create `slidemeister-common/` directory structure**

```
resources/assets/js/slidemeister-common/
├── types/
│   ├── editor.ts    (moved from slidemeister-editor/types/editor.ts)
│   └── api.ts       (moved from slidemeister-editor/types/api.ts)
└── composables/
    ├── useApi.ts    (moved from slidemeister-editor/composables/useApi.ts)
    └── useHtmlSerializer.ts  (refactored — see step 3)
```

Move `editor.ts` and `api.ts` as-is (no changes needed — they're already pure type files with no store imports).

Move `useApi.ts` as-is (already pure — uses only `window.BASE_URL` and `window.TOKEN`).

**Step 2: Refactor `useHtmlSerializer` to pure functions**

Current signature (store-coupled):
```typescript
export function useHtmlSerializer(editorStore: ReturnType<typeof useEditorStore>) {
  function serializeElement(el: SlideElement): string { ... }
  function serializeAll(): string {
    return editorStore.sortedElements.map((el) => serializeElement(el)).join('\n')
  }
  return { serializeElement, serializeAll }
}
```

New signature (pure functions in `slidemeister-common/composables/useHtmlSerializer.ts`):
```typescript
import type { SlideElement } from '@common/types/editor'

function escapeAttr(value: string): string {
  return value
    .replace(/&/g, '&amp;')
    .replace(/"/g, '&quot;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
}

export function serializeElement(el: SlideElement): string {
  // Same implementation as current serializeElement — no changes needed
  const p = el.properties
  const c = p.coordinates
  // ... rest identical
}

export function serializeElements(elements: Record<string, SlideElement>): string {
  return Object.values(elements)
    .sort((a, b) => b.properties.zIndex - a.properties.zIndex)
    .map(serializeElement)
    .join('\n')
}
```

**Step 3: Add `@common` alias to editor's vite.config.ts**

```typescript
resolve: {
  alias: {
    '@': path.resolve(__dirname),
    '@common': path.resolve(__dirname, '../slidemeister-common'),
  },
},
```

**Step 4: Update all editor imports**

Find and replace across the editor codebase:
- `@/types/editor` → `@common/types/editor`
- `@/types/api` → `@common/types/api`
- `@/composables/useApi` → `@common/composables/useApi`
- `@/composables/useHtmlSerializer` → `@common/composables/useHtmlSerializer`

In `App.vue`, change the useHtmlSerializer call from:
```typescript
const htmlSerializer = useHtmlSerializer(editorStore)
```
to importing the named exports:
```typescript
import { serializeElements } from '@common/composables/useHtmlSerializer'
```
Then update all call sites from `htmlSerializer.serializeAll()` to `serializeElements(editorStore.elements)` and from `htmlSerializer.serializeElement(el)` to `serializeElement(el)`.

**Step 5: Delete old files from editor**

Delete:
- `slidemeister-editor/types/editor.ts`
- `slidemeister-editor/types/api.ts`
- `slidemeister-editor/composables/useApi.ts`
- `slidemeister-editor/composables/useHtmlSerializer.ts`

**Step 6: Verify editor still builds**

Run: `cd resources/assets/js/slidemeister-editor && npx vite build`
Expected: Build succeeds with no errors.

**Step 7: Commit**

```bash
git add -A resources/assets/js/slidemeister-common/ resources/assets/js/slidemeister-editor/
git commit -m "refactor: extract shared code to slidemeister-common"
```

---

### Task 2: Create generator Vite project scaffold

Set up the basic project with entry point, vite config, and empty App.vue.

**Files:**
- Create: `resources/assets/js/slidemeister-generator/vite.config.ts`
- Create: `resources/assets/js/slidemeister-generator/main.ts`
- Create: `resources/assets/js/slidemeister-generator/App.vue`
- Create: `resources/assets/js/slidemeister-generator/env.d.ts`
- Create: `resources/assets/js/slidemeister-generator/tsconfig.json`

**Step 1: Create `vite.config.ts`**

```typescript
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

const laravelRoot = path.resolve(__dirname, '../../../../../..')

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname),
      '@common': path.resolve(__dirname, '../slidemeister-common'),
    },
  },
  build: {
    outDir: path.resolve(laravelRoot, 'public/build/slidemeister-generator'),
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: path.resolve(__dirname, 'main.ts'),
    },
  },
  server: {
    port: 5175,
    origin: 'http://localhost:5175',
  },
})
```

**Step 2: Create `main.ts`**

```typescript
import { createApp } from 'vue'
import App from './App.vue'

const app = createApp(App)
app.mount('#app')
```

**Step 3: Create minimal `App.vue`**

```vue
<script setup lang="ts">
// Placeholder — will be filled in Task 4
</script>

<template>
  <div id="generator">
    <h1>Slidemeister Generator</h1>
    <p>Competition ID: {{ (window as any).COMPETITION_ID }}</p>
  </div>
</template>
```

**Step 4: Create `env.d.ts`**

```typescript
/// <reference types="vite/client" />

interface Window {
  TOKEN: string
  BASE_URL: string
  COMPETITION_ID: number
}
```

**Step 5: Create `tsconfig.json`**

Copy from `slidemeister-editor/tsconfig.json` if it exists, otherwise create:
```json
{
  "compilerOptions": {
    "target": "ES2020",
    "module": "ESNext",
    "moduleResolution": "bundler",
    "strict": true,
    "jsx": "preserve",
    "resolveJsonModule": true,
    "isolatedModules": true,
    "esModuleInterop": true,
    "lib": ["ES2020", "DOM", "DOM.Iterable"],
    "skipLibCheck": true,
    "noEmit": true,
    "paths": {
      "@/*": ["./*"],
      "@common/*": ["../slidemeister-common/*"]
    }
  },
  "include": [
    "env.d.ts",
    "**/*.ts",
    "**/*.vue",
    "../slidemeister-common/**/*.ts"
  ]
}
```

**Step 6: Verify it builds**

Run: `cd resources/assets/js/slidemeister-generator && npx vite build`
Expected: Build succeeds.

**Step 7: Commit**

```bash
git add resources/assets/js/slidemeister-generator/
git commit -m "feat(generator): scaffold Vite project for slide generator"
```

---

### Task 3: Create `useSlideReplacer` composable

Port the old Vue 2 replacement logic from `renderHelper.js` and `renderCompetition.js` to pure TypeScript.

**Files:**
- Create: `resources/assets/js/slidemeister-generator/composables/useSlideReplacer.ts`
- Create: `resources/assets/js/slidemeister-generator/types/generator.ts`

**Step 1: Create `types/generator.ts`**

```typescript
import type { SlideElement } from '@common/types/editor'

export interface GeneratedSlide {
  key: string              // e.g. "comingup", "entry_123"
  type: string             // e.g. "comingup", "entry", "participants"
  name: string             // e.g. "Coming up", "Entry #1"
  elements: Record<string, SlideElement>
  html: string             // serialized HTML from useHtmlSerializer
  id?: number              // entry ID (only for entry slides)
}

export interface CompetitionData {
  competition: {
    id: number
    name: string
    competition_type: {
      is_anonymous: boolean
    }
  }
  templates: Record<string, {
    id: number
    definitions: string    // JSON stringified SlideDefinitions
  }>
  entries: EntryData[]
  participants: string[]
  videos: VideoData[]
}

export interface EntryData {
  id: number
  title: string
  author: string
  description: string
  remote_type: string
  filesize_human: string
  previous_sort_position: string
  previous_author: string
  previous_title: string
  options_string: string
  custom_option: string
  [key: string]: unknown   // Allow dynamic properties like option_1, option_2, etc.
}

export interface VideoData {
  file_id: number
  preview: string
  data: Record<string, unknown>
}
```

**Step 2: Create `composables/useSlideReplacer.ts`**

```typescript
import type { SlideElement, SlideDefinitions } from '@common/types/editor'
import { serializeElements } from '@common/composables/useHtmlSerializer'
import type { GeneratedSlide, CompetitionData, EntryData } from '@/types/generator'

// ── Core replacement functions (ported from renderHelper.js) ──

function deepClone<T>(obj: T): T {
  return JSON.parse(JSON.stringify(obj))
}

/**
 * Replace <<placeholder>> patterns in an element's content.
 * Reads from properties.placeholder, writes to properties.content.
 * Supports single or array-based replacements.
 */
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
    // If there are still placeholders, update placeholder for chained replacements
    if (element.properties.content.includes('<<')) {
      element.properties.placeholder = content
    }
  }
}

/**
 * Replace <<placeholder>> patterns across ALL elements.
 */
function replaceContentGlobal(
  elements: Record<string, SlideElement>,
  name: string,
  value: string
): void {
  for (const element of Object.values(elements)) {
    replaceContent(element, name, value)
  }
}

/**
 * Strip any remaining <<...>> placeholders from element content.
 */
function stripLeftoverPlaceholders(element: SlideElement): void {
  element.properties.content = element.properties.content.replace(/<<.+?>>/g, '')
}

// ── Competition render functions (ported from renderCompetition.js) ──

/**
 * Render "Coming up", "Now", "End" support slides.
 * Replaces <<headline>> and <<body>> (competition name).
 */
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

/**
 * Render a competition entry slide.
 * Replaces all entry fields as <<field>>, lowercases remote_type, strips leftover.
 */
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

/**
 * Render participants slide.
 * Replaces <<participants>> with comma-joined list, strips leftover.
 */
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

// ── Orchestrator ──

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

/**
 * Generate all competition playlist slides from API data.
 */
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
      // Video data passed separately in save payload
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
```

**Step 3: Commit**

```bash
git add resources/assets/js/slidemeister-generator/composables/ resources/assets/js/slidemeister-generator/types/
git commit -m "feat(generator): add slide replacement engine and generator types"
```

---

### Task 4: Build the Generator UI

Create `App.vue` with fetch → process → preview grid → save flow, plus `SlidePreview.vue`.

**Files:**
- Modify: `resources/assets/js/slidemeister-generator/App.vue`
- Create: `resources/assets/js/slidemeister-generator/components/SlidePreview.vue`

**Step 1: Create `SlidePreview.vue`**

```vue
<script setup lang="ts">
defineProps<{
  html: string
  label: string
  isVideo?: boolean
  videoPreview?: string
}>()
</script>

<template>
  <div class="slide-preview">
    <div class="slide-wrapper">
      <div v-if="isVideo && videoPreview" class="slide-container">
        <img :src="videoPreview" class="video-preview" />
      </div>
      <div v-else class="slide-container" v-html="html" />
    </div>
    <div class="slide-label">{{ label }}</div>
  </div>
</template>

<style scoped>
.slide-preview {
  display: inline-block;
  margin: 0 15px 15px 0;
}

.slide-wrapper {
  width: 480px;
  height: 270px;
  overflow: hidden;
  background: #000;
  border: 1px solid #333;
}

.slide-container {
  width: 960px;
  height: 540px;
  position: relative;
  transform: scale(0.5);
  transform-origin: top left;
}

.video-preview {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.slide-label {
  text-align: center;
  padding: 4px;
  font-size: 12px;
  color: #666;
}
</style>
```

**Step 2: Build `App.vue`**

```vue
<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApi } from '@common/composables/useApi'
import { generateCompetitionPlaylist } from '@/composables/useSlideReplacer'
import SlidePreview from '@/components/SlidePreview.vue'
import type { CompetitionData, GeneratedSlide } from '@/types/generator'

const api = useApi()
const state = ref<'loading' | 'preview' | 'saving' | 'saved' | 'error'>('loading')
const errorMessage = ref('')
const competitionName = ref('')
const slides = ref<GeneratedSlide[]>([])
const competitionData = ref<CompetitionData | null>(null)

const competitionId = (window as any).COMPETITION_ID as number

onMounted(async () => {
  try {
    const data = await api.request<CompetitionData>(
      'GET',
      `/api/competitions/${competitionId}/playlist-data`
    )
    competitionData.value = data
    competitionName.value = data.competition.name
    slides.value = generateCompetitionPlaylist(data)
    state.value = 'preview'
  } catch (err) {
    errorMessage.value = err instanceof Error ? err.message : 'Failed to load data'
    state.value = 'error'
  }
})

async function savePlaylist() {
  if (!competitionData.value) return

  state.value = 'saving'
  try {
    const payload = {
      slides: slides.value
        .filter(s => !s.type.startsWith('video_'))
        .map(s => ({
          key: s.key,
          type: s.type,
          name: s.name,
          definitions: JSON.stringify({ elements: s.elements }),
          cached_html_preview: s.html,
          cached_html_final: s.html,
          ...(s.id !== undefined ? { id: s.id } : {}),
        })),
      videos: competitionData.value.videos.map((v, i) => ({
        key: `video_${i + 1}`,
        file_id: v.file_id,
        data: v.data,
      })),
    }

    await api.request('POST', `/api/competitions/${competitionId}/playlist`, payload)
    state.value = 'saved'
  } catch (err) {
    errorMessage.value = err instanceof Error ? err.message : 'Failed to save'
    state.value = 'error'
  }
}

function getVideoPreview(slide: GeneratedSlide): string {
  if (!competitionData.value) return ''
  const index = parseInt(slide.type.replace('video_', '')) - 1
  return competitionData.value.videos[index]?.preview ?? ''
}
</script>

<template>
  <div id="generator">
    <!-- Loading -->
    <div v-if="state === 'loading'" class="status">
      Loading competition data...
    </div>

    <!-- Error -->
    <div v-else-if="state === 'error'" class="status error">
      {{ errorMessage }}
    </div>

    <!-- Preview -->
    <div v-else-if="state === 'preview' || state === 'saving'">
      <div class="toolbar">
        <h2>Competition: {{ competitionName }}</h2>
        <div class="actions">
          <button
            class="btn btn-success"
            :disabled="state === 'saving'"
            @click="savePlaylist"
          >
            {{ state === 'saving' ? 'Saving...' : 'Save Playlist' }}
          </button>
        </div>
      </div>

      <div class="slides-grid">
        <SlidePreview
          v-for="slide in slides"
          :key="slide.key"
          :html="slide.html"
          :label="slide.name"
          :is-video="slide.type.startsWith('video_')"
          :video-preview="getVideoPreview(slide)"
        />
      </div>
    </div>

    <!-- Saved -->
    <div v-else-if="state === 'saved'" class="status success">
      Playlist saved successfully!
    </div>
  </div>
</template>

<style>
body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  background: #1a1a2e;
  color: #eee;
  padding: 20px;
}

.toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding: 10px 0;
  border-bottom: 1px solid #333;
}

.toolbar h2 {
  margin: 0;
  font-size: 18px;
}

.btn {
  padding: 8px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.btn-success {
  background: #28a745;
  color: white;
}

.btn-success:hover {
  background: #218838;
}

.btn-success:disabled {
  background: #666;
  cursor: not-allowed;
}

.slides-grid {
  display: flex;
  flex-wrap: wrap;
}

.status {
  text-align: center;
  padding: 40px;
  font-size: 18px;
}

.error {
  color: #ff6b6b;
}

.success {
  color: #51cf66;
}
</style>
```

**Step 3: Expose `request` from `useApi`**

The generator needs the raw `request()` function for the new endpoints. Modify `slidemeister-common/composables/useApi.ts` to also export `request` in the return object:

```typescript
return {
  request,    // <-- add this line
  getTemplate,
  saveTemplate,
  createTemplate,
  deleteTemplate,
  listFonts,
}
```

**Step 4: Verify it builds**

Run: `cd resources/assets/js/slidemeister-generator && npx vite build`
Expected: Build succeeds.

**Step 5: Commit**

```bash
git add resources/assets/js/slidemeister-generator/
git commit -m "feat(generator): build generator UI with preview grid and save"
```

---

### Task 5: Create Blade template and web route

Serve the generator SPA from Laravel.

**Files:**
- Create: `resources/views/slidemeister-generator/index.blade.php`
- Modify: `routes/web.php`

**Step 1: Create Blade template**

`resources/views/slidemeister-generator/index.blade.php`:

```blade
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Slidemeister Generator</title>

    @include('partymeister-slides::layouts.partials.slide_fonts')

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
    </style>
</head>

<body>
<div id="app"></div>

<script>
    window.TOKEN = '{{ $api_token }}';
    window.BASE_URL = '{{ config('app.url') }}';
    window.COMPETITION_ID = {{ $competition_id }};
</script>

@if(app()->environment('local') && file_exists(public_path('hot-slidemeister-generator')))
    <script type="module" src="http://localhost:5175/@vite/client"></script>
    <script type="module" src="http://localhost:5175/main.ts"></script>
@else
    @php
        $manifest = json_decode(file_get_contents(public_path('build/slidemeister-generator/.vite/manifest.json')), true);
        $entry = $manifest['main.ts'] ?? $manifest['resources/assets/js/slidemeister-generator/main.ts'] ?? null;
    @endphp
    @if($entry)
        @if(isset($entry['css']))
            @foreach($entry['css'] as $css)
                <link rel="stylesheet" href="/build/slidemeister-generator/{{ $css }}" />
            @endforeach
        @endif
        <script type="module" src="/build/slidemeister-generator/{{ $entry['file'] }}"></script>
    @endif
@endif
</body>
</html>
```

**Step 2: Add web route**

Add to `routes/web.php` after the existing slidemeister-editor route:

```php
Route::get('slidemeister-generator/competition/{competition}', function (\Partymeister\Competitions\Models\Competition $competition) {
    $apiToken = \Motor\Admin\Models\User::first()->api_token;

    return view('partymeister-slides::slidemeister-generator.index', [
        'competition_id' => $competition->id,
        'api_token' => $apiToken,
    ]);
})->middleware(['bindings'])->name('backend.slidemeister-generator.competition');
```

**Step 3: Commit**

```bash
git add resources/views/slidemeister-generator/ routes/web.php
git commit -m "feat(generator): add Blade template and web route"
```

---

### Task 6: Create API endpoint — GET playlist data

New controller in partymeister-competitions that returns all templates + entries + videos as JSON.

**Files:**
- Create: `../partymeister-competitions/src/Http/Controllers/Api/CompetitionPlaylistController.php`
- Modify: `../partymeister-competitions/routes/api.php`

**Step 1: Create the controller**

`/packages/partymeister-competitions/src/Http/Controllers/Api/CompetitionPlaylistController.php`:

```php
<?php

namespace Partymeister\Competitions\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Motor\Admin\Helpers\MediaHelper;
use Motor\Admin\Http\Controllers\Controller;
use Partymeister\Competitions\Http\Resources\EntryResource;
use Partymeister\Competitions\Models\Competition;
use Partymeister\Slides\Models\SlideTemplate;

class CompetitionPlaylistController extends Controller
{
    public function show(Competition $competition): JsonResponse
    {
        // Load entries exactly like the existing PlaylistsController@index
        $entryCollection = EntryResource::collection($competition->qualified_entries->load('competition'));
        $entries = $entryCollection->toArrayRecursive();

        foreach ($entries as $key => $entry) {
            if ($entries[$key]['filesize'] == 0) {
                $entries[$key]['filesize_human'] = ' ';
            }
            if ($entries[$key]['description'] == '') {
                $entries[$key]['description'] = ' ';
            }
            $entries[$key]['description'] = nl2br($entries[$key]['description']);

            if ($key > 0) {
                $entries[$key]['previous_sort_position'] = (strlen($key) == 1 ? '0'.$key : $key);
                $entries[$key]['previous_author'] = $entries[$key - 1]['author'];
                $entries[$key]['previous_title'] = $entries[$key - 1]['title'];
            } else {
                $entries[$key]['previous_sort_position'] = ' ';
                $entries[$key]['previous_author'] = ' ';
                $entries[$key]['previous_title'] = ' ';
            }

            $entries[$key]['options_string'] = '';
            foreach (Arr::get($entry, 'options', []) as $i => $option) {
                $entries[$key]['options_string'] .= ' '.$option['name'];
                $entries[$key]['option_'.($i + 1)] = $option['name'];
            }
            $entries[$key]['custom_option'] = Arr::get($entry, 'custom_option');
            $entries[$key]['options_string'] .= ' '.Arr::get($entry, 'custom_option');
        }

        // Handle anonymous competitions
        $participants = [];
        if ($competition->competition_type->is_anonymous) {
            foreach ($entries as $key => $entry) {
                $participants[] = $entry['author'];
                $entries[$key]['author'] = ' ';
                $entries[$key]['previous_author'] = ' ';
            }
        }
        shuffle($participants);

        // Load templates
        $templates = [];
        $templateTypes = [
            'coming_up', 'now', 'end', 'competition',
            'competition_entry_1', 'participants',
        ];
        foreach ($templateTypes as $type) {
            $template = SlideTemplate::where('template_for', $type)->first();
            if ($template) {
                $templates[$type] = [
                    'id' => $template->id,
                    'definitions' => $template->definitions,
                ];
            }
        }

        // Load videos
        $videos = [];
        foreach ($competition->file_associations as $fileAssociation) {
            $videos[] = [
                'file_id' => $fileAssociation->file->id,
                'preview' => MediaHelper::getFileInformation($fileAssociation->file, 'file', false, ['preview', 'thumb'])['preview'] ?? '',
                'data' => MediaHelper::getFileInformation($fileAssociation->file, 'file', false, ['preview', 'thumb']),
            ];
        }

        return response()->json([
            'competition' => [
                'id' => $competition->id,
                'name' => $competition->name,
                'competition_type' => [
                    'is_anonymous' => (bool) $competition->competition_type->is_anonymous,
                ],
            ],
            'templates' => $templates,
            'entries' => $entries,
            'participants' => $participants,
            'videos' => $videos,
        ]);
    }
}
```

**Step 2: Add API route**

In `../partymeister-competitions/routes/api.php`, inside the first `Route::group` (with `auth:api` middleware), add:

```php
Route::get('competitions/{competition}/playlist-data', 'CompetitionPlaylistController@show')
     ->name('competitions.playlist-data');
```

**Step 3: Commit**

```bash
cd ../partymeister-competitions
git add src/Http/Controllers/Api/CompetitionPlaylistController.php routes/api.php
git commit -m "feat(api): add GET endpoint for competition playlist data"
```

---

### Task 7: Create API endpoint — POST save playlist

Add the `store` method that reshapes JSON data into the format `PlaylistService::generateCompetitionPlaylist()` expects.

**Files:**
- Modify: `../partymeister-competitions/src/Http/Controllers/Api/CompetitionPlaylistController.php`
- Modify: `../partymeister-competitions/routes/api.php`

**Step 1: Add `store` method to controller**

```php
public function store(Competition $competition, Request $request): JsonResponse
{
    // Reshape JSON into the flat array format that PlaylistService expects
    $data = [
        'slide' => [],
        'type' => [],
        'name' => [],
        'cached_html_preview' => [],
        'cached_html_final' => [],
        'id' => [],
    ];

    foreach ($request->input('slides', []) as $slide) {
        $key = $slide['key'];
        $data['slide'][$key] = $slide['definitions'];
        $data['type'][$key] = $slide['type'];
        $data['name'][$key] = $slide['name'];
        $data['cached_html_preview'][$key] = $slide['cached_html_preview'] ?? '';
        $data['cached_html_final'][$key] = $slide['cached_html_final'] ?? '';
        if (isset($slide['id'])) {
            $data['id'][$key] = $slide['id'];
        }
    }

    // Add video slides
    foreach ($request->input('videos', []) as $video) {
        $key = $video['key'];
        $data['slide'][$key] = json_encode($video);
        $data['type'][$key] = $key;
        $data['name'][$key] = ucfirst(str_replace('_', ' ', $key));
    }

    \Partymeister\Slides\Services\PlaylistService::generateCompetitionPlaylist($competition, $data);

    return response()->json(['status' => 'ok']);
}
```

**Step 2: Add POST route**

In `../partymeister-competitions/routes/api.php`, after the GET route added in Task 6:

```php
Route::post('competitions/{competition}/playlist', 'CompetitionPlaylistController@store')
     ->name('competitions.playlist.store');
```

**Step 3: Commit**

```bash
cd ../partymeister-competitions
git add src/Http/Controllers/Api/CompetitionPlaylistController.php routes/api.php
git commit -m "feat(api): add POST endpoint for saving competition playlist"
```

---

### Task 8: Integration test — build and verify

Build the generator, verify the full flow works end-to-end.

**Step 1: Build the generator**

```bash
cd resources/assets/js/slidemeister-generator
npm install   # or use existing node_modules from editor
npx vite build
```

Expected: Build succeeds, files appear in `public/build/slidemeister-generator/`.

**Step 2: Verify in browser**

1. Navigate to `/slidemeister-generator/competition/{id}` (use a competition that has entries)
2. Verify: slides load and display at 50% scale
3. Verify: "Coming up", "Now", entry slides, "End" all show correct text replacements
4. Verify: `<<placeholder>>` patterns are fully replaced (no leftover `<<...>>` visible)
5. Verify: participants slide shows if competition is anonymous
6. Verify: video previews show thumbnail images
7. Click "Save Playlist" → verify success message
8. Check backend: playlist appears in playlist index with correct slides

**Step 3: Final commit**

```bash
git add -A
git commit -m "feat(generator): complete competition playlist generator"
```

---

### Task 9 (Optional): Add hot-reload dev file

For local development, create the hot file marker so Vite dev server works with the Blade template.

**Step 1: Add dev script to `package.json`**

If there's a root package.json, add a script. Otherwise, run manually:

```bash
cd resources/assets/js/slidemeister-generator
touch ../../../../../../public/hot-slidemeister-generator
npx vite
```

The Blade template checks for `public_path('hot-slidemeister-generator')` and loads from `localhost:5175` when it exists.

**Step 2: Clean up hot file on exit**

Add to vite.config.ts a plugin that creates/removes the hot file (same pattern as Laravel Vite plugin):

```typescript
// Add to plugins array:
{
  name: 'hot-file',
  configureServer(server) {
    const hotFile = path.resolve(laravelRoot, 'public/hot-slidemeister-generator')
    fs.writeFileSync(hotFile, 'http://localhost:5175')
    server.httpServer?.on('close', () => {
      fs.rmSync(hotFile, { force: true })
    })
  },
}
```

Don't forget to add `import fs from 'fs'` at the top of vite.config.ts.

**Step 3: Commit**

```bash
git add resources/assets/js/slidemeister-generator/vite.config.ts
git commit -m "feat(generator): add hot-reload dev file for local development"
```
