<template>
  <div class="properties-panel">
    <div v-if="!el" class="no-selection">No element selected</div>
    <template v-else>
      <div class="panel-header">{{ el.properties.prettyname || el.name }}</div>

      <div class="panel-body">
        <!-- Identity -->
        <details open>
          <summary>Identity</summary>
          <div class="section">
            <label>
              <span>Pretty Name</span>
              <input type="text" :value="el.properties.prettyname" @input="set('properties.prettyname', ($event.target as HTMLInputElement).value)" />
            </label>
            <label>
              <span>Placeholder</span>
              <input type="text" :value="el.properties.placeholder" @input="set('properties.placeholder', ($event.target as HTMLInputElement).value)" />
            </label>
          </div>
        </details>

        <!-- Typography -->
        <details open>
          <summary>Typography</summary>
          <div class="section">
            <label>
              <span>Font Family</span>
              <select :value="el.properties.fontFamily" @change="set('properties.fontFamily', ($event.target as HTMLSelectElement).value)">
                <option v-for="f in fonts" :key="f" :value="f">{{ f }}</option>
              </select>
            </label>
            <label>
              <span>Font Size</span>
              <input type="number" :value="el.properties.fontSize" min="1" @input="set('properties.fontSize', Number(($event.target as HTMLInputElement).value))" />
            </label>
            <label>
              <span>Font Weight</span>
              <select :value="el.properties.fontWeight" @change="set('properties.fontWeight', ($event.target as HTMLSelectElement).value)">
                <option v-for="w in fontWeights" :key="w.value" :value="w.value">{{ w.label }}</option>
              </select>
            </label>
            <label>
              <span>Font Stretch</span>
              <select :value="el.properties.fontStretch" @change="set('properties.fontStretch', ($event.target as HTMLSelectElement).value)">
                <option v-for="s in fontStretches" :key="s" :value="s">{{ s }}%</option>
              </select>
            </label>
            <label>
              <span>Font Style</span>
              <select :value="el.properties.fontStyle" @change="set('properties.fontStyle', ($event.target as HTMLSelectElement).value)">
                <option value="normal">Normal</option>
                <option value="italic">Italic</option>
              </select>
            </label>
            <label>
              <span>Font Kerning</span>
              <input type="text" :value="el.properties.fontKerning" @input="set('properties.fontKerning', ($event.target as HTMLInputElement).value)" />
            </label>
          </div>
        </details>

        <!-- Layout -->
        <details open>
          <summary>Layout</summary>
          <div class="section">
            <div class="field-row">
              <span class="field-label">Text Align</span>
              <div class="btn-group">
                <button :class="{ active: el.properties.textAlign === 'left' }" @click="set('properties.textAlign', 'left')" title="Left">L</button>
                <button :class="{ active: el.properties.textAlign === 'center' }" @click="set('properties.textAlign', 'center')" title="Center">C</button>
                <button :class="{ active: el.properties.textAlign === 'right' }" @click="set('properties.textAlign', 'right')" title="Right">R</button>
              </div>
            </div>
            <div class="field-row">
              <span class="field-label">Vertical Align</span>
              <div class="btn-group">
                <button :class="{ active: el.properties.verticalAlign === 'flex-start' }" @click="set('properties.verticalAlign', 'flex-start')" title="Top">T</button>
                <button :class="{ active: el.properties.verticalAlign === 'center' }" @click="set('properties.verticalAlign', 'center')" title="Middle">M</button>
                <button :class="{ active: el.properties.verticalAlign === 'flex-end' }" @click="set('properties.verticalAlign', 'flex-end')" title="Bottom">B</button>
              </div>
            </div>
            <label>
              <span>Line Height</span>
              <input type="text" :value="el.properties.lineHeight" @input="set('properties.lineHeight', ($event.target as HTMLInputElement).value)" />
            </label>
            <label>
              <span>Text Shadow</span>
              <input type="text" :value="el.properties.textShadow" @input="set('properties.textShadow', ($event.target as HTMLInputElement).value)" />
            </label>
            <label>
              <span>Text Transform</span>
              <select :value="el.properties.textTransform" @change="set('properties.textTransform', ($event.target as HTMLSelectElement).value)">
                <option value="none">None</option>
                <option value="uppercase">Uppercase</option>
                <option value="lowercase">Lowercase</option>
                <option value="capitalize">Capitalize</option>
              </select>
            </label>
          </div>
        </details>

        <!-- Colors -->
        <details open>
          <summary>Colors</summary>
          <div class="section">
            <div class="field-row">
              <span class="field-label">Color</span>
              <ColorInput :model-value="el.properties.color" @update:model-value="set('properties.color', $event)" />
            </div>
            <div class="field-row">
              <span class="field-label">Background</span>
              <ColorInput :model-value="el.properties.backgroundColor" @update:model-value="set('properties.backgroundColor', $event)" />
            </div>
            <div class="field-row">
              <span class="field-label">Opacity</span>
              <div class="range-row">
                <input type="range" min="0" max="1" step="0.05" :value="el.properties.opacity" @input="set('properties.opacity', Number(($event.target as HTMLInputElement).value))" />
                <span class="range-value">{{ el.properties.opacity.toFixed(2) }}</span>
              </div>
            </div>
          </div>
        </details>

        <!-- Behavior -->
        <details open>
          <summary>Behavior</summary>
          <div class="section">
            <label class="checkbox-label">
              <input type="checkbox" :checked="el.properties.locked" @change="set('properties.locked', ($event.target as HTMLInputElement).checked)" />
              <span>Locked</span>
            </label>
            <label class="checkbox-label">
              <input type="checkbox" :checked="el.properties.editable" @change="set('properties.editable', ($event.target as HTMLInputElement).checked)" />
              <span>Editable</span>
            </label>
            <label class="checkbox-label">
              <input type="checkbox" :checked="el.properties.snapping" @change="set('properties.snapping', ($event.target as HTMLInputElement).checked)" />
              <span>Snapping</span>
            </label>
            <label>
              <span>Visibility</span>
              <select :value="el.properties.visibility" @change="set('properties.visibility', ($event.target as HTMLSelectElement).value)">
                <option value="render">Render</option>
                <option value="preview">Preview</option>
              </select>
            </label>
          </div>
        </details>

        <!-- Mode -->
        <details open>
          <summary>Mode</summary>
          <div class="section">
            <div class="field-row">
              <div class="btn-group wide">
                <button :class="{ active: el.properties.resizable && !el.properties.warpable }" @click="setMode('resize')">Resize</button>
                <button :class="{ active: el.properties.warpable }" @click="setMode('warp')">Warp</button>
              </div>
            </div>
          </div>
        </details>

        <!-- Size -->
        <details open>
          <summary>Size</summary>
          <div class="section">
            <div class="field-row">
              <div class="btn-group wide">
                <button :class="{ active: el.properties.size === 'individual' }" @click="set('properties.size', 'individual')">Individual</button>
                <button :class="{ active: el.properties.size === 'fill' }" @click="set('properties.size', 'fill')">Fill</button>
              </div>
            </div>
          </div>
        </details>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useEditorStore } from '@/stores/editorStore'
import ColorInput from './ColorInput.vue'

const editorStore = useEditorStore()

const el = computed(() => editorStore.activeElement)

const fonts = [
  'Arial',
  'Verdana',
  'Georgia',
  'Times New Roman',
  'Courier New',
  'Helvetica',
  'Impact',
  'Comic Sans MS',
]

const fontWeights = [
  { value: '100', label: '100 Thin' },
  { value: '200', label: '200 Extra Light' },
  { value: '300', label: '300 Light' },
  { value: '400', label: '400 Normal' },
  { value: '500', label: '500 Medium' },
  { value: '600', label: '600 Semi Bold' },
  { value: '700', label: '700 Bold' },
  { value: '800', label: '800 Extra Bold' },
  { value: '900', label: '900 Black' },
]

const fontStretches = ['75', '87.5', '100', '112.5', '125']

function set(path: string, value: unknown): void {
  if (!editorStore.activeElementName) return
  editorStore.updateElementProperty(editorStore.activeElementName, path, value)
}

function setMode(mode: 'resize' | 'warp'): void {
  if (!editorStore.activeElementName) return
  if (mode === 'resize') {
    set('properties.resizable', true)
    set('properties.warpable', false)
  } else {
    set('properties.resizable', false)
    set('properties.warpable', true)
  }
}
</script>

<style scoped>
.properties-panel {
  width: 300px;
  background: #1e1e1e;
  color: #ddd;
  font-size: 12px;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  overflow-y: auto;
  height: 100%;
  border-left: 1px solid #333;
  user-select: none;
}

.no-selection {
  padding: 24px 16px;
  text-align: center;
  color: #666;
  font-style: italic;
}

.panel-header {
  padding: 10px 12px;
  font-weight: 600;
  font-size: 13px;
  background: #252525;
  border-bottom: 1px solid #333;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.panel-body {
  padding-bottom: 16px;
}

details {
  border-bottom: 1px solid #333;
}

details summary {
  padding: 8px 12px;
  cursor: pointer;
  font-weight: 600;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #999;
  background: #252525;
  user-select: none;
}

details summary:hover {
  color: #ccc;
  background: #2a2a2a;
}

details[open] summary {
  color: #bbb;
}

.section {
  padding: 8px 12px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.section label {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.section label > span:first-child {
  font-size: 11px;
  color: #888;
}

.section input[type='text'],
.section input[type='number'],
.section select {
  padding: 4px 6px;
  background: #2a2a2a;
  border: 1px solid #555;
  border-radius: 4px;
  color: #ddd;
  font-size: 12px;
}

.section input[type='text']:focus,
.section input[type='number']:focus,
.section select:focus {
  outline: none;
  border-color: #7aa2f7;
}

.section select {
  cursor: pointer;
}

.field-row {
  display: flex;
  align-items: center;
  gap: 8px;
}

.field-label {
  font-size: 11px;
  color: #888;
  min-width: 70px;
  flex-shrink: 0;
}

.btn-group {
  display: flex;
  gap: 0;
}

.btn-group button {
  padding: 4px 10px;
  background: #2a2a2a;
  border: 1px solid #555;
  color: #aaa;
  font-size: 11px;
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
}

.btn-group button:first-child {
  border-radius: 4px 0 0 4px;
}

.btn-group button:last-child {
  border-radius: 0 4px 4px 0;
}

.btn-group button:not(:first-child) {
  border-left: none;
}

.btn-group button:hover {
  background: #333;
  color: #ddd;
}

.btn-group button.active {
  background: #7aa2f7;
  color: #fff;
  border-color: #7aa2f7;
}

.btn-group.wide {
  flex: 1;
}

.btn-group.wide button {
  flex: 1;
  text-align: center;
}

.checkbox-label {
  flex-direction: row !important;
  align-items: center;
  gap: 6px !important;
}

.checkbox-label input[type='checkbox'] {
  accent-color: #7aa2f7;
  cursor: pointer;
}

.range-row {
  display: flex;
  align-items: center;
  gap: 8px;
  flex: 1;
}

.range-row input[type='range'] {
  flex: 1;
  accent-color: #7aa2f7;
  cursor: pointer;
}

.range-value {
  font-size: 11px;
  color: #aaa;
  font-family: monospace;
  min-width: 32px;
  text-align: right;
}
</style>
