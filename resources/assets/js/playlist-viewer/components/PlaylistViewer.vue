<template>
    <div class="playlist-viewer">
        <div class="slides-container">
            <div
                v-for="(item, index) in playlist.items"
                :key="index"
                :id="'slide-' + index"
                class="slide"
                :class="{ 'slide--active': index === current }"
            >
                <img v-if="item.type === 'image'" :src="item.file.file_original">
                <video
                    v-if="item.type === 'video'"
                    muted
                    loop
                    :id="'video-' + index"
                >
                    <source :src="item.file.file_original" type="video/mp4">
                </video>
            </div>
        </div>

        <div class="playlist-viewer__actions" v-if="playlist.items.length > 1">
            <button class="playlist-viewer__nav-button" @click="prev">&lsaquo;</button>
            <div class="playlist-viewer__dots">
                <button
                    v-for="(item, index) in playlist.items"
                    :key="index"
                    class="playlist-viewer__dot"
                    :class="{ 'playlist-viewer__dot--current': index === current }"
                    @click="goTo(index)"
                />
            </div>
            <button class="playlist-viewer__nav-button" @click="next">&rsaquo;</button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
    playlist: { type: Object, required: true },
})

const current = ref(0)
let autoplayTimer = null

function getVideo(index) {
    return document.getElementById('video-' + index)
}

function pauseVideo(index) {
    const el = getVideo(index)
    if (el) el.pause()
}

function playVideo(index) {
    const el = getVideo(index)
    if (el) {
        setTimeout(() => {
            el.pause()
            el.currentTime = 0
            el.play()
        }, 500)
    }
}

function goTo(index) {
    const item = props.playlist.items[current.value]
    if (item?.type === 'video') pauseVideo(current.value)

    current.value = index

    const newItem = props.playlist.items[index]
    if (newItem?.type === 'video') playVideo(index)

    resetAutoplay()
}

function next() {
    goTo((current.value + 1) % props.playlist.items.length)
}

function prev() {
    goTo((current.value - 1 + props.playlist.items.length) % props.playlist.items.length)
}

function resetAutoplay() {
    clearInterval(autoplayTimer)
    autoplayTimer = setInterval(next, 10000)
}

onMounted(() => {
    const item = props.playlist.items[0]
    if (item?.type === 'video') {
        const el = getVideo(0)
        if (el) el.play()
    }
    resetAutoplay()
})

onUnmounted(() => {
    clearInterval(autoplayTimer)
})
</script>

<style>
.playlist-viewer {
    position: relative;
}

.slides-container {
    position: relative;
    overflow: hidden;
}

.slide {
    display: none;
    align-items: center;
    box-sizing: border-box;
    width: 100%;
    justify-content: center;
}

.slide--active {
    display: flex;
}

.slide img {
    object-fit: cover;
    object-position: center;
    width: 100%;
}

.slide video {
    width: 100%;
}

.playlist-viewer__actions {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 20px;
}

.playlist-viewer__nav-button {
    background: transparent;
    border: none;
    color: #ccc;
    cursor: pointer;
    font-size: 24px;
    transition-duration: 0.3s;
}

.playlist-viewer__nav-button:hover {
    color: #888;
}

.playlist-viewer__dots {
    display: flex;
    gap: 10px;
    margin: 0 10px;
}

.playlist-viewer__dot {
    background-color: #eee;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: block;
    height: 10px;
    width: 10px;
    padding: 0;
    transition-duration: 0.3s;
}

.playlist-viewer__dot--current,
.playlist-viewer__dot:hover {
    background-color: #888;
}
</style>
