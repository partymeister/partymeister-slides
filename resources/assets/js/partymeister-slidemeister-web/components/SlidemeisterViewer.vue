<template>
  <main class="main">
    <script id="vertexShader" type="x-shader/x-vertex">
            varying vec2 vUv;
            void main()	{
                vUv = uv;
                gl_Position = vec4( position, 1.0 );
            }

    </script>

    <div id="cables-container" style="width: 100%; height: 100%;">
      <canvas id="glcanvas" width="100vw" height="100vh" tabindex="1"></canvas>
    </div>
    <div id="shader-container"></div>
    <div class="debug alert alert-danger d-none">
      CachedPlaylists: {{ cachedPlaylists.length }}<br>
      Playlist: {{ playlist.name }}<br>
      Items: {{ items.length }}<br>
      CurrentItem: {{ currentItem }}<br>
      <button @click="deleteStorage" class="btn btn-sm btn-primary btn-block">Empty cache</button>
      <button v-if="standalone" @click="goToConfiguration" class="btn btn-sm btn-primary btn-block">Server
        configuration
      </button>
      <vue-audio style="display: none;" id="jingle-player" :file="jingle"/>
    </div>

    <template v-if="(currentItem != null || this.playNow) && current !== undefined">
      <img v-if="current.type === 'image' && (current.file_association !== null && current.file_association !== undefined)"
           :src="playlistImages[this.currentItem] ? playlistImages[this.currentItem].src : current.file_association.file.url" class="img-fluid slide current"
           :style="{'opacity': currentOpacity}">
      <div v-if="current.type === 'image' && current.slide && current.slide.cached_html_final !== ''"
           v-html="current.slide.cached_html_final" class="slidemeister-instance slide current"
           :style="{'opacity': currentOpacity, 'zoom': zoom}"></div>
      <video v-if="current.type === 'video'" id="video-current" class="slide current"
             :style="{'opacity': currentOpacity}">
        <source :src="current.file_association.file.url" type="video/mp4">
      </video>
    </template>

    <template v-if="(previousItem != null || this.playNow) && previous !== undefined">
      <img v-if="previous.type === 'image' && (previous.file_association !== null && previous.file_association !== undefined)"
           :src="playlistImages[this.previousItem] ? playlistImages[this.previousItem].src : previous.file_association.file.url"
           class="img-fluid slide previous">
      <div v-if="previous.type === 'image' && previous.slide && previous.slide.cached_html_final !== ''"
           v-html="previous.slide.cached_html_final" class="slidemeister-instance slide previous"
           :style="{'zoom': zoom}"></div>
      <video v-if="previous.type === 'video'" id="video-previous" class="slide previous">
        <source :src="previous.file_association.file.url" type="video/mp4">
      </video>
    </template>

    <template class="next-item" v-if="(nextItem != null || this.playNow) && next !== undefined">
      <img v-if="next.type === 'image' && (next.file_association !== null && next.file_association !== undefined)"
           :src="playlistImages[this.nextItem] ? playlistImages[this.nextItem].src : next.file_association.file.url" class="img-fluid slide next"
           :style="{'opacity': nextOpacity}">
      <div v-if="next.type === 'image' && next.slide && next.slide.cached_html_final !== ''"
           v-html="next.slide.cached_html_final" class="slidemeister-instance slide next"
           :style="{'opacity': nextOpacity, 'zoom': zoom}"></div>
      <video v-if="next.type === 'video'" id="video-next" class="slide next" :style="{'opacity': nextOpacity}">
        <source :src="next.file_association.file.url" type="video/mp4">
      </video>
    </template>
    <div id="fake-element">

    </div>
  </main>
</template>

<script>

const axios = require('axios');
import Vue from 'vue';
import keybindings from "../mixins/keybindings";
import jingles from "../mixins/jingles";
import siegmeister from "../mixins/siegmeister";
import shader from "../mixins/shader";
import VueAudio from 'vue-audio';
import toast from "../mixins/toast";
import echo from "../mixins/echo";

import WebMidi from 'webmidi';

WebMidi.enable(function (err) {
  if (err) console.log("An error occurred", err);
}, true);

export default {
  name: 'partymeister-slidemeister-web',
  props: ['standalone'],
  components: {
    VueAudio
  },
  mixins: [
    keybindings,
    jingles,
    siegmeister,
    shader,
    toast,
    echo,
  ],
  data: function () {
    return {
      currentOpacity: 1,
      nextOpacity: 0,
      zoom: 2,
      cachedPlaylists: [],
      configuration: {},

      clearPlayNowAfter: false,
      playNow: false,
      playNowItems: [],
      currentPlayNowItem: null,
      nextPlayNowItem: null,
      currentItemSaved: null,

      playlistSaved: {},
      playlist: {},
      playlistImages: [],
      items: [],
      currentItem: null,
      previousItem: null,
      nextItem: null,
      callbackTimeout: null,
      slideTimeout: null,
      currentBackground: null,
      transitionGroups: [
        ['animate__fadeIn', 'animate__fadeOut'],
        // ['pulse', 'fadeOut'],
        // ['zoomIn', 'zoomOut'],
      ],
    };
  },
  mounted() {
    // Cables code goes here
    //   setTimeout(() => {
    //     shader.animate();
    //   }, 1000);
  },
  computed: {
    // a computed getter
    current: function () {
      console.log('CURRENT updated');
      if (this.playNow && this.playNowItems[this.currentPlayNowItem] !== undefined) {
        // console.log('playnow current', this.playNowItems[this.currentPlayNowItem]);
        return this.playNowItems[this.currentPlayNowItem];
      }
      return this.items[this.currentItem];
    },
    next: function () {
      console.log('NEXT updated');
      if (this.clearPlayNowAfter) {
        return this.items[this.nextItem];
      }
      if (this.playNow && this.playNowItems[this.nextPlayNowItem] !== undefined) {
        // console.log('playnow next', this.playNowItems[this.nextPlayNowItem]);
        return this.playNowItems[this.nextPlayNowItem];
      }
      return this.items[this.nextItem];
    },
    previous: function () {
      return this.items[this.previousItem];
    },
  },
  methods: {
    goToConfiguration() {
      this.$router.push({name: 'configuration'});
    },
    seekToPlayNow() {
      this.clearTimeouts();
      this.playNow = true;
      this.next;
      this.$forceNextTick(() => {
        this.beforeSeek();
        this.playTransition();
      });
    },
    afterSeek() {
      console.log('AFTERSEEK');
      localStorage.setItem('currentItem', this.currentItem);

      if (this.currentItem && parseInt(this.items[this.currentItem].midi_note) > 0) {
        console.log("CurrentItem and Midi Note Exist");
        if (WebMidi.outputs.length > 0) {
          console.log("We have a midi device");
          WebMidi.outputs[0].playNote(parseInt(this.items[this.currentItem].midi_note), 1, {
            velocity: 1,
            duration: 1000
          });
          console.log("Played midi note " + this.items[this.currentItem].midi_note + ' to device ' + WebMidi.outputs[0].name + ' (' + WebMidi.outputs[0].id + ')');
        } else {
          console.log('We do not have a midi device');
        }
      } else {
        console.log("SKIPPED MIDI");
      }

      if (!this.playnow) {
        this.checkVideo();
        // this.animateBackground();
        this.setCallbackDelay();
        this.setSlideTimeout();
      }
      this.updateStatus();

    },
    seekToIndex(index) {
      console.log('SEEK TO INDEX ' + index);

      this.clearTimeouts();

      let currentItem;

      if (this.items[index] !== undefined) {
        currentItem = index;
      } else {
        console.log('Cannot seek to ' + index);
        return;
      }

      console.log("SET CURRENT ITEM TO", currentItem, index);

      if (currentItem === 0) {
        this.nextItem = 0;
      } else {
        this.nextItem = currentItem;
        currentItem = currentItem - 1;
      }

      this.next;
      this.prepareTransition(currentItem, true);




      return;
      this.clearTimeouts();

      if (this.items[index] !== undefined) {
        if ((index + 1) > this.items.length) {
          this.nextItem = 0;
        } else {
          this.nextItem = index + 1;
        }
        this.currentItem = index;
      } else {
        this.currentItem = 0;
        this.nextItem = 0;
      }
      if (this.currentItem === null) {
        console.log("set current item");
        this.currentItem = 0;
        this.nextItem = 0;
      }

      if (this.previousItem !== null) {
        this.beforeSeek();
        if (!hard) {
          setTimeout(() => {
            this.playTransition();
          }, 10);
        } else {
          this.previousItem = null;
          this.afterSeek();
        }
      }

    },
    prepareTransition(currentItem, hard) {
      this.beforeSeek(hard);
      if (!hard) {
        setTimeout(() => {
          if (this.next.slide_type !== 'slidemeister_winners') {
            this.deleteBars();
          }
          this.playTransition();
        }, 250);
      } else {
        this.currentItem = this.nextItem;

        this.currentOpacity = 1;
        this.nextOpacity = 0;
        this.afterSeek();
      }
    },
    seekToNextItem(hard) {
      console.log('[SEEK] next', hard);
      this.clearTimeouts();

      let currentItem = this.currentItem;
      if (this.playNow) {
        currentItem = this.currentItemSaved;
      }

      if (this.items[currentItem + 1] !== undefined) {
        this.nextItem = currentItem + 1;
      } else {
        this.nextItem = 0;
      }
      if (this.items[currentItem - 1] !== undefined) {
        this.previousItem = currentItem - 1;
      } else {
        this.previousItem = this.items.length - 1;
      }
      this.next;
      this.prepareTransition(currentItem, hard);
    },
    seekToPreviousItem(hard) {
      console.log('[SEEK] previous', hard);
      this.clearTimeouts();

      let currentItem = this.currentItem;
      if (this.playNow) {
        currentItem = this.currentItemSaved;
      }

      if (this.items[currentItem - 1] !== undefined) {
        this.nextItem = currentItem - 1;
      } else {
        this.nextItem = this.items.length - 1;
      }
      if (this.items[currentItem + 1] !== undefined) {
        this.previousItem = currentItem + 1;
      } else {
        this.previousItem = 0;
      }
      this.next;
      this.prepareTransition(currentItem, hard);
    },
    checkVideo() {
      if (this.current && this.current.type === 'video') {
        setTimeout(() => {
          let currentVideo = document.getElementById("video-current");
          if (currentVideo != null) {
            currentVideo.currentTime = 0;
            currentVideo.play();
          }
          let previousVideo = document.getElementById("video-previous");
          if (previousVideo != null) {
            previousVideo.pause();
          }
        }, 10);
      }
    },
    playTransition(transition, duration) {
      this.clearSiegmeisterBars();

      let transitionGroup = this.transitionGroups[Math.floor(Math.random() * this.transitionGroups.length)];
      // if (transition !== 255 && transition !== '') {
      //   transitionGroup = this.transitionGroups[parseInt(transition)];
      // }
      // if (transitionGroup === undefined || transitionGroup.length !== 2) {
      //   transitionGroup = this.transitionGroups[Math.floor(Math.random() * this.transitionGroups.length)];
      // }

      this.currentOpacity = 1;
      this.nextOpacity = 1;

      console.log("[TRANSITION] start fade in");
      this.animateCSS('.next', transitionGroup[0], () => {
        console.log('[TRANSITION] done - swapping items');
        document.querySelector('.next').style.zIndex = 1001;
        if (this.clearPlayNowAfter) {
          this.playNow = false;
          this.clearPlayNowAfter = false;
        }
        this.current;
        this.next;
        this.$forceUpdate();

        if (this.playNow) {
          console.log("post transition playnow management");
          this.currentPlayNowItem = this.nextPlayNowItem;
        } else {
          this.currentItem = this.nextItem;
        }
        this.nextOpacity = 0;
        setTimeout(() => {
          document.querySelector('.next').style.zIndex = 999;
          this.afterSeek();
        }, 0);
      });

      console.log("[TRANSITION] start fade out");
      this.animateCSS('.current', transitionGroup[1], () => {
      });
    },
    setSlideTimeout() {
      if (this.playNow || this.currentItem === null) {
        return;
      }
      if (this.items.length === 0) {
        return;
      }
      if (!this.items[this.currentItem].is_advanced_manually) {
        console.log("there should be a timeout starting now");
        // console.log('Setting timeout to ' + this.items[this.currentItem].duration);
        this.slideTimeout = setTimeout(() => {
          this.seekToNextItem();
        }, this.items[this.currentItem].duration * 1000)
      }
    },
    setCallbackDelay() {
      if (this.currentItem === 'playnow') {
        console.log("[PLAY NOW]");
        return;
      }
      if (this.playlist.callbacks !== undefined && this.playlist.callbacks) {
        // console.log('Setting callback timeout to ' + this.items[this.currentItem].callback_delay);
        if (this.items[this.currentItem].callback_hash !== '') {
          this.callbackTimeout = setTimeout(() => {
            // console.log('Excuting callback ' + this.items[this.currentItem].callback_hash);
            axios.get(this.playlist.callback_url + this.items[this.currentItem].callback_hash).then(result => {
              console.log('[CALLBACK] successful');
            }).catch(e => {
              console.log('[CALLBACK] Error');
            });
          }, this.items[this.currentItem].callback_delay * 1000)
        }
      }
    },
    clearTimeouts() {
      // console.log('Clearing timeouts');
      window.clearTimeout(this.callbackTimeout);
      window.clearTimeout(this.slideTimeout);
    },

    beforeSeek(hard = false) {
      if (this.nextItem === false || this.nextItem === undefined || this.nextItem === null) {
        console.log("SKIPPING BEFORE SEEK");
        return;
      }
      // if (this.nextItem && parseInt(this.items[this.nextItem].midi_note) > 0) {
      //   console.log("CurrentItem and Midi Note Exist");
      //   if (WebMidi.outputs.length > 0) {
      //     console.log("We have a midi device");
      //     WebMidi.outputs[0].playNote(parseInt(this.items[this.nextItem].midi_note), 1, {
      //       velocity: 1,
      //       duration: 1000
      //     });
      //     console.log("Played midi note " + this.items[this.nextItem].midi_note + ' to device ' + WebMidi.outputs[0].name + ' (' + WebMidi.outputs[0].id + ')');
      //   } else {
      //     console.log('We do not have a midi device');
      //   }
      // } else {
      //   console.log("SKIPPED MIDI");
      // }


      // Add html to a hidden element so we can search it. THIS SUCKS but it's the web and js and html and... yeah I don't care...
      let competition = null;
      if (this.items[this.nextItem] && this.items[this.nextItem].slide && this.items[this.nextItem].slide.cached_html_final && this.items[this.nextItem].slide_type !== 'compo') {

        const tempWrapper = document.createElement('div');
        tempWrapper.innerHTML = this.items[this.nextItem].slide.cached_html_final;

        let fakeElement = document.getElementById('fake-element');

        if (fakeElement) {
          document.getElementById('fake-element').replaceChildren(tempWrapper);

          competition = document.getElementById('fake-element').querySelector('[data-partymeister-slides-prettyname="competition"]');
          // if (competition) {
          //   console.log("GETTING NEW COMPETITION NAME", this.items[this.nextItem].slide.id, competition.innerText);
          // }
        }
      }

      if (this.items[this.nextItem] && this.currentBackground === this.items[this.nextItem].slide_type && !competition && this.items[this.nextItem].slide_type !== 'compo') {
        // console.log('Correct background is already playing, skipping');
        this.currentBackground = this.items[this.nextItem].slide_type;
        this.clearSiegmeisterBars();
        return;
      }

      this.currentBackground = this.items[this.nextItem].slide_type;
      this.clearSiegmeisterBars();

      // if (this.currentBackground === '') {
      //   this.currentBackground = 'announce';
      // }

      console.log("[BEFORE SEEK]] set background to: ", this.currentBackground);
      CABLES.patch.setVariable('SLIDETYPE', this.currentBackground); // only for deadline / darya
      switch (this.currentBackground) {
        case 'siegmeister_winners':
          CABLES.patch.setVariable('currentSlide', {scene: 1, transition: !hard, time: Date.now()});
          break;
        case 'siegmeister_bars':
          CABLES.patch.setVariable('currentSlide', {scene: 1, transition: !hard, time: Date.now()});
          break;
        case 'comingup':
          let comingupSlide = {scene: 2, transition: !hard, time: Date.now()};
          console.log("COMING UP", comingupSlide);
          // console.log(comingupSlide, competition.innerText);
          CABLES.patch.setVariable('currentSlide', comingupSlide);

          setTimeout(() => {
            CABLES.patch.setVariable("slideTypeString", "COMING UP");
            if (competition.innerText.length > 8) {
              CABLES.patch.setVariable("eventOrCompoName", '');
              CABLES.patch.setVariable("eventOrCompoNameLong", competition.innerText);
              console.log("eventOrCompoNameLong", competition.innerText);
            } else {
              CABLES.patch.setVariable("eventOrCompoNameLong", '');
              CABLES.patch.setVariable("eventOrCompoName", competition.innerText);
              console.log("eventOrCompoName", competition.innerText);
            }
          }, 500);

          break;
        case 'now':
          let nowSlide = {scene: 2, transition: !hard, time: Date.now()};
          console.log("NOW", nowSlide);
          CABLES.patch.setVariable('currentSlide', nowSlide);

          setTimeout(() => {
            CABLES.patch.setVariable("slideTypeString", "NOW");
            if (competition.innerText.length > 8) {
              CABLES.patch.setVariable("eventOrCompoName", '');
              CABLES.patch.setVariable("eventOrCompoNameLong", competition.innerText);
              console.log("eventOrCompoNameLong", competition.innerText);
            } else {
              CABLES.patch.setVariable("eventOrCompoNameLong", '');
              CABLES.patch.setVariable("eventOrCompoName", competition.innerText);
              console.log("eventOrCompoName", competition.innerText);
            }
          }, 500);

          break;
        case 'end':
          let endSlide = {scene: 2, transition: !hard, time: Date.now()};
          console.log("END", endSlide);
          CABLES.patch.setVariable('currentSlide', endSlide);

          setTimeout(() => {
            CABLES.patch.setVariable("slideTypeString", "END");
            if (competition.innerText.length > 8) {
              CABLES.patch.setVariable("eventOrCompoName", '');
              CABLES.patch.setVariable("eventOrCompoNameLong", competition.innerText);
              console.log("eventOrCompoNameLong", competition.innerText);
            } else {
              CABLES.patch.setVariable("eventOrCompoNameLong", '');
              CABLES.patch.setVariable("eventOrCompoName", competition.innerText);
              console.log("eventOrCompoName", competition.innerText);
            }
          }, 500);

          break;
        case 'comments':
          CABLES.patch.setVariable('currentSlide', {scene: 1, transition: !hard, time: Date.now()});
          break;
        case 'announce':
          CABLES.patch.setVariable('currentSlide', {scene: 1, transition: !hard, time: Date.now()});
          break;
        case 'announce_important':
          CABLES.patch.setVariable('currentSlide', {scene: 1, transition: !hard, time: Date.now()});
          break;
        case 'compo':


          try {
            this.items[this.nextItem].metadata = JSON.parse(this.items[this.nextItem].metadata);
          } catch (e) {
            // do nothing
          }

          console.log("Metadata", this.items[this.nextItem].metadata);
          let remoteType = 'party';
          if (this.items[this.nextItem].metadata?.remote_type != '') {
            remoteType = this.items[this.nextItem].metadata?.remote_type;
           // remoteType = this.items[this.nextItem].metadata?.remote_type.toLowerCase();
          }
          let compoSlide = {scene: 3, transition: !hard, entryType: remoteType.toLowerCase(), time: Date.now()};
          console.log("COMPO", compoSlide);
          CABLES.patch.setVariable('currentSlide', compoSlide);
          break;
        case 'timetable':
          console.log("TIMETABLE");
          CABLES.patch.setVariable('currentSlide', {scene: 1, transition: !hard, time: Date.now()});
          break;
        default:
          CABLES.patch.setVariable('currentSlide', {scene: 0, transition: !hard, time: Date.now()});
      }

    },
    updateStatus() {
      // console.log('Update status');

      let currentItem = this.items[this.currentItem];
      let currentItemId = null;

      if (currentItem !== undefined) {
        currentItemId = currentItem.id;
      }

      let data = {
        playlists: this.cachedPlaylists.map(playlist => {
          return {id: playlist.id, updated_at: new Date(playlist.updated_at.date).getTime() / 1000}
        }),
        currentPlaylist: this.playlist.id,
        currentItem: currentItemId,
      };

      if (this.configuration.server !== undefined) {
        axios.post(this.configuration.server + '/ajax/slidemeister-web/' + this.configuration.client + '/status', data).then(response => {
          // console.log('Updated status');
        });
      }

    },
    resizeWindow() {
      let scaleX = window.innerWidth / 960;
      let scaleY = window.innerHeight / 540;

      this.zoom = Math.min(scaleX, scaleY);
    },
    animateCSS(element, animationName, callback) {
      const node = document.querySelector(element);
      if (node === null) {
        console.error('Node ' + element + ' not found - skipping');
        return;
      }
      node.classList.add('animate__animated', animationName, 'animate__delay_05s');

      function handleAnimationEnd() {
        node.classList.remove('animate__animated', animationName);
        node.removeEventListener('animationend', handleAnimationEnd);

        if (typeof callback === 'function') callback()
      }

      node.addEventListener('animationend', handleAnimationEnd);
    },
    deleteStorage() {
      this.cachedPlaylists = [];
      this.playlist = {};
      this.items = [];
      this.playlistImages = [];
      this.currentItem = null;
      document.querySelectorAll('canvas').forEach((element) => {
        element.style.zIndex = 0;
      });
      this.fragmentShader = '';
      // this.unloadScene();

      localStorage.clear();
      this.updateStatus();
    },
    getSlideClientConfiguration() {
      let configuration = localStorage.getItem('slideClientConfiguration');
      if (configuration !== undefined && configuration !== null) {
        configuration = JSON.parse(configuration);
        Vue.set(this, 'configuration', configuration.configuration);
      }
    },
  },
  created() {
    this.$eventHub.$on('show-viewer', () => {
      window.addEventListener('keydown', this.addListener, false);
    });
    this.$eventHub.$on('slide-client-loaded', () => {
      this.getSlideClientConfiguration();
    });

    this.getSlideClientConfiguration();

    window.onresize = () => {
      this.resizeWindow();
    };

    setTimeout(() => {
      this.resizeWindow();
    }, 0);

    // Check if we have playlists in local storage
    if (this.cachedPlaylists.length === 0) {
      let cachedPlaylists = localStorage.getItem('cachedPlaylists');
      if (cachedPlaylists !== undefined && cachedPlaylists != null) {
        this.cachedPlaylists = JSON.parse(cachedPlaylists);
      }
    }
    if (Object.keys(this.playlist).length === 0) {
      let playlist = localStorage.getItem('playlist');
      if (playlist !== undefined && playlist != null) {
        this.playlist = JSON.parse(playlist);
        this.items = this.playlist.items;

        // preload images
        // FIXME: actually preload images
        this.items.forEach((item) => {
          if (item.type === 'image') {
            let i = new Image();
            if (item.file_association !== null) {
              i.src = item.file_association.file.url;
            }
            // this.playlistImages.push(i);
          }
          // this.playlistImages = [];
        });

      }
    }

    if (this.currentItem === null) {
      let currentItem = localStorage.getItem('currentItem');
      if (currentItem !== undefined && currentItem !== null) {
        // Delay is necessary to correctly load the background shader on first load
        setTimeout(() => {
          this.seekToIndex(parseInt(currentItem), true);
        }, 500);
      } else {
        this.seekToIndex(0, true);
      }
    }

  }
}

</script>
<style lang="scss">
main {
  cursor: none;
  background: black;
}

canvas {
  display: block;
  position: absolute;
  outline: 0;
  background-color: #000000;
  overflow: hidden
}

body {
  background-color: black;
  overflow: hidden;
}

main {
  position: absolute;
  width: 100%;
  height: 100%;
}

main .slide {
  position: absolute;
  width: 100%;
  height: auto;
}

main .debug {
  position: absolute;
  z-index: 10000;
  opacity: 0.9;
}

main .previous {
  z-index: 9000;
}

main .current {
  z-index: 9100;
}

.slidemeister-instance {
  width: 960px;
  height: 540px;
  zoom: 2;
}

.slidemeister-bars {
  position: absolute;
  opacity: 0.5;
  background-color: black;
}

.slidemeister-bars.active {
  z-index: 10000;
}

.blink {
  animation: blinker 1s linear infinite;
}

@keyframes blinker {
  50% {
    opacity: 0.1;
  }
}

.current {
  z-index: 1000;
  width: 960px;
  height: 540px;
}

.previous {
  visibility: hidden;
  z-index: 998;
  width: 960px;
  height: 540px;
}

.next {
  /*visibility: hidden;*/
  z-index: 999;
  width: 960px;
  height: 540px;
}

div[data-partymeister-slides-visibility='preview'] {
  display: none;
}

.medium-editor-element {
  z-index: 10000;
  width: 98%;
  margin: 0 auto;
  text-align: left;
  font-family: Arial, sans-serif;
}

.hidden {
  display: none;
}

.medium-editor-element p {
  margin-bottom: 0;
}

.moveable {
  display: flex;
  font-family: "Roboto", sans-serif;
  z-index: 1000;
  position: absolute;
  width: 300px;
  height: 200px;
  text-align: center;
  font-size: 40px;
  margin: 0 auto;
  font-weight: 100;
  background-size: cover;
  background-repeat: no-repeat;
  background-position: center center;
}

.movable span {
  font-size: 10px;
}

.snappable-shadow {
  width: 200px;
  height: 200px;
  /*background-color: red;*/
  position: absolute;
  visibility: hidden;
}

#cables-container {
  position: absolute;
}

#shader-container {
  position: absolute;
  width: 1920px;
  height: 1080px;
}

#fake-element {
  display: none;
}
</style>
