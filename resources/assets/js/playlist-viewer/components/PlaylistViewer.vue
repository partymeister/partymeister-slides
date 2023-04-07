<template>
    <div>
        <agile :autoplaySpeed="10000" :autoplay="true" @afterChange="afterChange($event)"
               @beforeChange="beforeChange($event)">
            <div v-for="(item, index) in playlist.items" class="slide" :id="'slide-'+index">
                <img v-if="item.type == 'image'" :src="item.file.file_original">
                <video muted loop autoplay :id="'video-'+index" v-if="item.type == 'video'">
                    <source :src="item.file.file_original" type="video/x-m4v">
                </video>
            </div>
        </agile>
    </div>
</template>
<script>

    import {VueAgile} from 'vue-agile'

    export default {
        props: [
            'playlist'
        ],
        data: () => ({
            slides: [],
        }),
        components: {
            agile: VueAgile
        },
        methods: {
            beforeChange(event) {
                let currentSlide = this.playlist.items[event.currentSlide];
                if (currentSlide.type === 'video') {
                    let element = document.getElementById('video-' + event.currentSlide);
                    element.pause();
                    console.log("PAUSE PLAYING");
                }
            },
            afterChange(event) {
                let currentSlide = this.playlist.items[event.currentSlide];
                if (currentSlide.type === 'video') {
                    let element = document.getElementById('video-' + event.currentSlide);
                    setTimeout(() => {
                        console.log(element.currentTime);
                        element.pause();
                        element.currentTime = 0;
                        element.play();
                        console.log("START PLAYING");
                    }, 500);
                }
            }
        },
        mounted() {
        }
    }
</script>
<style>
    video {
        width: 100%;
    }

    .thumbnails {
        margin: 0 -5px;
        width: calc(100% + 10px);
    }
    .agile__actions {
        margin-top: 20px;
    }

    .agile__nav-button {
        background: transparent;
        border: none;
        color: #ccc;
        cursor: pointer;
        font-size: 24px;
        -webkit-transition-duration: 0.3s;
        transition-duration: 0.3s;
    }

    .thumbnails .agile__nav-button {
        position: absolute;
        top: 50%;
        -webkit-transform: translateY(-50%);
        transform: translateY(-50%);
    }

    .thumbnails .agile__nav-button--prev {
        left: -45px;
    }

    .thumbnails .agile__nav-button--next {
        right: -45px;
    }

    .agile__nav-button:hover {
        color: #888;
    }

    .agile__dot {
        margin: 0 10px;
    }

    .agile__dot button {
        background-color: #eee;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        display: block;
        height: 10px;
        font-size: 0;
        line-height: 0;
        margin: 0;
        padding: 0;
        -webkit-transition-duration: 0.3s;
        transition-duration: 0.3s;
        width: 10px;
    }

    .agile__dot--current button, .agile__dot:hover button {
        background-color: #888;
    }

    .slide {
        -webkit-box-align: center;
        align-items: center;
        box-sizing: border-box;
        color: #fff;
        display: -webkit-box;
        display: flex;
        width: 100%;
        -webkit-box-pack: center;
        justify-content: center;
    }

    .slide--thumbniail {
        cursor: pointer;
        padding: 0 5px;
        -webkit-transition: opacity 0.3s;
        transition: opacity 0.3s;
    }

    .slide--thumbniail:hover {
        opacity: 0.75;
    }
    .slide--thumbniail img {
        width: 24%;
    }

    .slide img {
        -o-object-fit: cover;
        object-fit: cover;
        -o-object-position: center;
        object-position: center;
        width: 100%;
    }
</style>
