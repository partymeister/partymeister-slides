<template>
    <div id="playlist-item-container">
        <input type="hidden" id="dropped-files" :value="JSON.stringify(droppedFiles)">
        <draggable v-model="droppedFiles" :options="{group:'files'}" @add="onAdd" style="min-height: 100px;"
                   class="row">
            <div v-for="(file, index) in droppedFiles" class="col-md-3" style="border: 1px dotted red;">
                <div class="item-number">{{ index+1 }}</div>
                <div class="item-delete">
                    <button type="button" @click="deleteFile(file)"><i class="fa fa-trash-alt"></i>
                    </button>
                </div>
              <div v-if="isHtmlSlide(file)" class="image-wrapper">
                <img v-if="file.slide && file.slide.file_preview" :src="file.slide.file_preview.conversions.preview" class="img-fluid">
              </div>
                <div v-else-if="isImage(file)" class="image-wrapper">
                    <div>{{ filetype(file) }}</div>
                    <img v-if="file.file && file.file.conversions" :src="file.file.conversions.preview" class="img-fluid">
                  <img v-if="file.file_association" :src="file.file_association.file.conversions.preview" class="img-fluid">
                </div>
                <div v-else> {{ getFileType(file).file_name }}</div>
                <div>
                    <div>
                        {{ $t('partymeister-slides.backend.playlists.duration') }} <input type="text" name="duration" v-model="file.duration" size="4"> {{ $t('partymeister-slides.backend.playlists.seconds') }}
                        <input type="checkbox" name="is_advanced_manually" v-model="file.is_advanced_manually"> {{ $t('partymeister-slides.backend.playlists.is_advanced_manually') }}
                    </div>
                    <div>
                        {{ $t('partymeister-slides.backend.playlists.transition') }}
                        <select name="transition_id" v-model="file.transition.identifier">
                            <option v-for="(transition, index) in transitions" :value="transition.identifier">
                                {{ transition.name }}
                            </option>
                        </select>
                        <select name="transition_slidemeister_id" v-model="file.transition_slidemeister.identifier">
                            <option v-for="(transition, index) in slidemeisterTransitions" :value="transition.identifier">
                                {{ transition.name }}
                            </option>
                        </select>
                        <input type="text" name="transition_duration" size="4" v-model="file.transition_duration"> {{ $t('partymeister-slides.backend.playlists.milliseconds') }}
                    </div>
                    <div>
                        {{ $t('partymeister-slides.backend.playlists.midi_note') }} <input type="text" size="2" name="midi_note" v-model="file.midi_note">
                        Slide type override
                        <select name="overwrite_slide_type" v-model="file.overwrite_slide_type">
                            <option value="">Default</option>
                            <option v-for="(slideType, index) in slideTypes" :value="slideType.value">
                                {{ slideType.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        {{ $t('partymeister-slides.backend.playlists.callback') }}
                        <select name="callback_hash" v-model="file.callback_hash" style="width: 80%;">
                            <option value="">{{ $t('partymeister-slides.backend.callbacks.no_callback') }}</option>
                            <option v-for="(callback, index) in callbacks" :value="callback.hash">
                                {{ callback.name }}
                            </option>
                        </select>
                        {{ $t('partymeister-slides.backend.playlists.callback_delay') }}
                        <input type="text" name="callback_delay" v-model="file.callback_delay" size="4"> {{ $t('partymeister-slides.backend.playlists.seconds') }}

                        <div><strong>Filename: {{ getFileType(file).file_name }}</strong></div>
                    </div>
                </div>
            </div>
        </draggable>
    </div>
</template>

<script>
    import draggable from 'vuedraggable';

    function IsJsonString(str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    }

    export default {
        name: 'partymeister-slides-playlist',
        props: ['files'],
        data: function() {
            return {
                droppedFiles: [],
                transitions: [],
                slidemeisterTransitions: [],
                callbacks: [],
                slideTypes: [{name: 'announce', value: 'announce'},{name: 'announce no bg', value: 'announce_nobg'}, {name: 'compo', value: 'compo'},
                  {name: 'comingup', value: 'comingup'}, {name: 'now', value: 'now'}, {name: 'end', value: 'end'}]
            };
        },
        components: {
            draggable,
        },
        methods: {
            onAdd: function (event) {
                let fakeObject = Object.assign({}, this.droppedFiles[event.newIndex]);
                fakeObject.duration = 20;
                fakeObject.midi_note = 0;
                fakeObject.transition_identifier = 255;
                fakeObject.transition = { identifier: 255};
                fakeObject.transition_slidemeister_identifier = 255;
                fakeObject.transition_slidemeister = { identifier: 255};
                fakeObject.transition_duration = 2000;
                fakeObject.callback_hash = '';
                fakeObject.overwrite_slide_type = '';
                fakeObject.callback_delay = 20;
                fakeObject.is_advanced_manually = false;
                fakeObject.slide = Object.assign({}, this.droppedFiles[event.newIndex]);
                Vue.set(this.droppedFiles, event.newIndex, fakeObject);
                console.log("droppedItem", fakeObject);
                // Vue.set(this.droppedFiles[event.newIndex], 'duration', 20);
                // Vue.set(this.droppedFiles[event.newIndex], 'midi_note', 0);
                // Vue.set(this.droppedFiles[event.newIndex], 'transition', {identifier: 255})
                // Vue.set(this.droppedFiles[event.newIndex], 'transition_slidemeister', {identifier: 255});
                // Vue.set(this.droppedFiles[event.newIndex], 'transition_duration', 2000);
                // Vue.set(this.droppedFiles[event.newIndex], 'callback_hash', '');
                // Vue.set(this.droppedFiles[event.newIndex], 'overwrite_slide_type', '');
                // Vue.set(this.droppedFiles[event.newIndex], 'callback_delay', 20);
                // Vue.set(this.droppedFiles[event.newIndex], 'is_advanced_manually', false);
            },
            filetype: function(file) {
              let data = this.getFileType(file);
              if (data === null) {
                return 'unknown';
              }
                if (data.mime_type === 'image/png' || data.mime_type === 'image/jpg' || data.mime_type === 'image/jpeg') {
                    return 'Image';
                } else if (data.mime_type === 'video/x-m4v' || data.mime_type === 'video/mp4') {
                    return 'Video';
                }
              return 'unknown';
            },
          isHtmlSlide: (file) => {
              if (file.slide && file.slide.cached_html_preview !== '') {
                return true;
              }
          },
            isImage: function (file) {
              if (!file.transition) {
                file.transition = { identifier: 255};
                file.transition_slidemeister = { identifier: 255};
              }
              let data = this.getFileType(file);
                if (data.mime_type === 'image/png' || data.mime_type === 'image/jpg' || data.mime_type === 'image/jpeg' || data.mime_type === 'video/x-m4v' || data.mime_type === 'video/mp4') {
                    return true;
                }
                return false;
            },
            deleteFile: function (file) {
                this.droppedFiles.splice(this.droppedFiles.indexOf(file), 1);
            },
          getFileType(file) {
            let data = { file_name: 'unknown', mime_type: 'unknown'};
            if (file.cached_html_final && file.file_preview !== null) {
              data = file.file_preview;
            } else if (file.file) {
              data = file.file
            } else if (file.file_association) {
              data = file.file_association.file;
            }
            return data;
          },
        },
        mounted: function () {
          this.$eventHub.$on('screenshot-updated', (data) => {
            for (let file of this.droppedFiles) {
              if (data.slide.id == file.slide.id) {
                file.slide.file_preview = { conversions: { preview: data.slide.media.preview_url } };
              }
            }
          });

            let files = [];
            if (IsJsonString(this.files)) {
                files = JSON.parse(this.files);
            }
            if (files) {
                this.droppedFiles = files;
            }

            axios.get(route('ajax.transitions.index')).then((response) => {
                for (const [index, transition] of response.data.data.entries()) {
                    if (transition.client_type === 'screens') {
                        this.transitions.push(transition);
                    } else {
                        this.slidemeisterTransitions.push(transition);
                    }
                }
            });

            axios.get(route('ajax.callbacks.index')+'?per_page=1000').then((response) => {
                this.callbacks = response.data.data;
            });
        }
    }
</script>
<style lang="scss">
    #playlist-item-container .sortable-ghost {
        opacity: 0.7;
        max-width: 25%;
    }

    #playlist-item-container .sortable-ghost .card-body {
        display: none;
    }

    #playlist-item-container select {
        max-width: 100px !important;
    }

    #playlist-item-container .col-md-3 {
        position: relative;
        padding: 3px;
    }

    #playlist-item-container .item-number, #playlist-item-container .item-delete {
        z-index: 1100;
        text-align: center;
        width: 40px;
        height: 35px;
        font-size: 20px;
        line-height: 35px;
        font-weight: bold;
        background: white;
        border-bottom-right-radius: 10px;
        position: absolute;
        top: 3px;
        left: 3px;
    }

    #playlist-item-container .item-delete {
        background-color: red;
        border-bottom-left-radius: 10px;
        border-bottom-right-radius: 0;
        right: 3px;
        left: auto;
    }

    .item-delete button {
        color: white;
        background: none;
        border: none;
        padding: 0;
        margin: 0;
    }

    .item-delete button:focus, .item-delete button:active {
        outline: none !important;
        box-shadow: none;
    }

    .image-wrapper {
        width: 100%;
        position: relative;
        padding-top: 56.25%;
        overflow: hidden;
        margin-bottom: 5px;
    }

    .image-wrapper div {
        z-index: 1100;
        position: absolute;
        background-color: white;
        top: 30px;
        width: 40%;
        height: 35px;
        line-height: 35px;
        left: 30%;
        border-radius: 10px;
        text-align: center;
        font-size: 20px;
    }

    #playlist-item-container .image-wrapper .img-fluid {
        z-index: 1000;
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
    }


</style>
