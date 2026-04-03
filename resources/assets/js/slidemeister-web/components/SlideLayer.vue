<template>
  <div class="slide-layer" :style="slideLayerStyle">
    <!-- Displayed slide (currently visible) -->
    <SlideRenderer
      ref="displayedRef"
      :item="displayedItem"
      :class="displayedAnimClass"
      :style="{ animationDuration: `${durationMs}ms` }"
    />

    <!-- Incoming slide (animates in on top during transition) -->
    <SlideRenderer
      v-if="incomingItem"
      ref="incomingRef"
      :item="incomingItem"
      :class="incomingAnimClass"
      :style="{ zIndex: 1, animationDuration: `${durationMs}ms` }"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, nextTick } from 'vue'
import SlideRenderer from './SlideRenderer.vue'
import type { PlaylistItem } from '@/types/playlist'

// Transition map: identifier -> [inClass, outClass]
// These use animate.css class names
const TRANSITION_MAP: Record<string, [string, string]> = {
  '0':   ['animate__fadeIn',         'animate__fadeOut'],          // Crossfade
  '1':   ['animate__rotateIn',       'animate__rotateOut'],        // Rotate
  '2':   ['animate__fadeInRight',    'animate__fadeOutLeft'],       // Speed
  '3':   ['animate__bounceIn',       'animate__bounceOut'],        // Bounce
  '4':   ['animate__flipInY',        'animate__flipOutY'],         // Flip
  '5':   ['animate__pulse',          'animate__fadeOut'],           // Pulse
  '6':   ['animate__zoomIn',         'animate__zoomOut'],           // Schwomp
  '7':   ['animate__wobble',         'animate__fadeOut'],           // Wobble
  '8':   ['animate__zoomIn',         'animate__zoomOut'],           // Zoom
  '9':   ['animate__zoomInDown',     'animate__zoomOutUp'],         // Zoom 2
  '10':  ['animate__rollIn',         'animate__rollOut'],           // Roll
}
const DEFAULT_TRANSITION: [string, string] = ['animate__fadeIn', 'animate__fadeOut']

const props = defineProps<{
  zoom: number
  windowWidth: number
  windowHeight: number
}>()

const slideLayerStyle = computed(() => {
  const scaledW = 960 * props.zoom
  const scaledH = 540 * props.zoom
  const offsetX = (props.windowWidth - scaledW) / 2
  const offsetY = (props.windowHeight - scaledH) / 2
  return {
    transform: `translate(${offsetX}px, ${offsetY}px) scale(${props.zoom})`,
    transformOrigin: 'top left',
  }
})

const emit = defineEmits<{
  transitionComplete: []
}>()

const displayedItem = ref<PlaylistItem | undefined>(undefined)
const incomingItem = ref<PlaylistItem | undefined>(undefined)

const displayedAnimClass = ref('')
const incomingAnimClass = ref('')
const durationMs = ref(400)

const displayedRef = ref<InstanceType<typeof SlideRenderer> | null>(null)
const incomingRef = ref<InstanceType<typeof SlideRenderer> | null>(null)

let inFlight = false
let safetyTimeout: ReturnType<typeof setTimeout> | null = null

function getTransitionPair(identifier?: string): [string, string] {
  if (identifier === '255') {
    const keys = Object.keys(TRANSITION_MAP)
    return TRANSITION_MAP[keys[Math.floor(Math.random() * keys.length)]]
  }
  if (identifier !== undefined && TRANSITION_MAP[identifier]) {
    return TRANSITION_MAP[identifier]
  }
  return DEFAULT_TRANSITION
}

/**
 * Navigate to a new item.
 * - hard=true: instant swap, no animation
 * - hard=false: animate.css transition using the item's transition config
 */
function transition(targetItem: PlaylistItem, isHard: boolean): void {
  // Cancel any in-flight transition
  if (inFlight) {
    commitTransition()
  }

  if (isHard) {
    // Instant swap
    displayedRef.value?.pauseVideo()
    displayedItem.value = targetItem
    nextTick(() => {
      if (targetItem.type === 'video') {
        displayedRef.value?.playVideo()
      }
    })
    emit('transitionComplete')
    return
  }

  // Soft transition with animation
  const identifier = targetItem.transition_slidemeister?.identifier
  const duration = targetItem.transition_duration || 400
  const [inClass, outClass] = getTransitionPair(identifier)

  durationMs.value = duration
  inFlight = true

  // Pause outgoing video
  displayedRef.value?.pauseVideo()

  // Mount the incoming renderer with the target item
  incomingItem.value = targetItem
  incomingAnimClass.value = ''
  displayedAnimClass.value = ''

  // Wait one frame for the incoming renderer to mount, then start animations
  nextTick(() => {
    incomingAnimClass.value = `animate__animated ${inClass}`
    displayedAnimClass.value = `animate__animated ${outClass}`

    // Safety timeout — force complete if animation doesn't fire
    safetyTimeout = setTimeout(() => {
      commitTransition()
    }, duration + 500)

    // Listen for animation end on the incoming element
    const incomingEl = incomingRef.value?.$el
    if (incomingEl) {
      const onEnd = (e: AnimationEvent) => {
        // Only handle animations on the renderer itself, not bubbled from children
        if (e.target !== incomingEl) return
        incomingEl.removeEventListener('animationend', onEnd)
        commitTransition()
      }
      incomingEl.addEventListener('animationend', onEnd)
    }
  })
}

function commitTransition(): void {
  if (!inFlight) return
  inFlight = false

  if (safetyTimeout) {
    clearTimeout(safetyTimeout)
    safetyTimeout = null
  }

  const arrived = incomingItem.value
  if (!arrived) return

  // Swap: incoming becomes displayed
  displayedItem.value = arrived
  incomingItem.value = undefined
  displayedAnimClass.value = ''
  incomingAnimClass.value = ''

  // Play video if the new slide is a video
  nextTick(() => {
    if (arrived.type === 'video') {
      displayedRef.value?.playVideo()
    }
  })

  emit('transitionComplete')
}

/**
 * Set the displayed item without animation (for initial load / restore).
 */
function setDisplayed(item: PlaylistItem): void {
  displayedItem.value = item
}

defineExpose({ transition, setDisplayed })
</script>

<style scoped>
.slide-layer {
  position: fixed;
  top: 0;
  left: 0;
  width: 960px;
  height: 540px;
  overflow: hidden;
  cursor: none;
}
</style>
