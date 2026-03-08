<template>
  <div>
    <a v-if="slide.media && slide.media.preview_url" :data-caption="record.description" data-fancybox="gallery" :href="slide.media.original_url">
      <img :id="'slide_'+slide.id" style="max-width: 150px;" class="img-thumbnail" :src="slide.media.preview_url"/>
    </a>
    <a v-else-if="media && media.preview_url" :data-caption="record.description" data-fancybox="gallery" :href="media.original_url">
      <img style="max-width: 150px;" class="img-thumbnail" :src="media.preview_url"/>
    </a>
    <p v-else>
      No file available
    </p>
  </div>
</template>

<script setup>
import { reactive, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    record: Object,
    media: Object
});

const eventBus = window.eventBus;

const slide = reactive({ media: {}, id: 0 });

function updateSlide(slideData) {
    slide.id = slideData.id;
    slide.media = slideData.media;
    $(document.querySelector('#slide_' + slide.id)).effect('highlight');
}

function onScreenshotUpdated(data) {
    if (data.slide.id !== props.record.id) {
        return;
    }
    updateSlide(data.slide);
}

onMounted(() => {
    eventBus.on('screenshot-updated', onScreenshotUpdated);
});

onUnmounted(() => {
    eventBus.off('screenshot-updated', onScreenshotUpdated);
});
</script>

<style lang="scss">
</style>
