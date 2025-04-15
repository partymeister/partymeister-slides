<template>
  <div>
    <a v-if="slide.media.preview_url" :data-caption="record.description" data-fancybox="gallery" :href="slide.media.original_url">
      <img :id="'slide_'+slide.id" style="max-width: 150px;" class="img-thumbnail" :src="slide.media.preview_url"/>
    </a>
    <a v-else-if="media.preview_url" :data-caption="record.description" data-fancybox="gallery" :href="media.original_url">
      <img style="max-width: 150px;" class="img-thumbnail" :src="media.preview_url"/>
    </a>
    <p v-else>
      No file available
    </p>
  </div>
</template>

<script>

const axios = require('axios');
import Vue from 'vue';

export default {
  props: ['record', 'media'],
  name: 'partymeister-slides-screenshot',
  data: function () {
    return {
      slide: {media: {}, id: 0},
    };
  },
  mounted() {
    this.$eventHub.$on('screenshot-updated', (data) => {
      if (data.slide.id !== this.record.id) {
        return;
      }
      this.updateSlide(data.slide);
    });
  },
  watch: {
  },
  computed: {
  },
  methods: {
    updateSlide: function(slide) {
      this.slide.id = slide.id;
      this.slide.media = slide.media;
      $(document.querySelector('#slide_'+this.slide.id)).effect('highlight');
    }
  }
}

</script>
<style lang="scss">
</style>
