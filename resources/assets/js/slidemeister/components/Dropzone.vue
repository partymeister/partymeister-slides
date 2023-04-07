<template>
    <div>
        <div id="dropzone" :style="{'zIndex': zIndex}" class="d-none">
            <div>
                Drop it like it's hawt!
            </div>
            <draggable style="visibility: hidden;" v-model="droppedFiles" :options="{group:'files'}" @add="onAdd" class="draggable-container">
            </draggable>
        </div>
    </div>
</template>

<script>
    import draggable from 'vuedraggable';

    export default {
        name: 'partymeister-slides-dropzone',
        data: function () {
            return {
                droppedFiles: [],
                zIndex: -1,
                dragInProgress: false,
                interval: false,
            };
        },
        components: { draggable },
        methods: {
            onDragLeave($event) {
                let elements = document.querySelectorAll('.medium-editor-element');
                for (let element of elements) {
                    element.style.pointerEvents = 'auto';
                }
                document.querySelector('#dropzone').classList.add('d-none');
                this.zIndex = -1;
                if ($event !== undefined) {
                    $event.preventDefault();
                    $event.stopPropagation();
                }
            },
            onDragEnter($event) {
                $event.preventDefault();
                $event.stopPropagation();
            },
            onDragOver($event) {
                this.dragInProgress = Date.now();
                this.zIndex = 13000;
                let elements = document.querySelectorAll('.medium-editor-element');
                for (let element of elements) {
                    element.style.pointerEvents = 'none';
                }
                document.querySelector('#dropzone').classList.remove('d-none');
                $event.preventDefault();
            },
            onDrop($event) {
                this.zIndex = -1;
                let file = null;
                if ($event.dataTransfer.files.length > 0) {
                    file = $event.dataTransfer.files[0];
                }
                if (file) {
                    let reader = new FileReader();
                    reader.readAsDataURL(file);
                    reader.onloadend = () => {
                        let image = new Image();
                        image.onload = () => {
                            this.$eventHub.$emit('partymeister-slides:image-dropped-from-dataurl', {
                                file: file,
                                dataUrl: reader.result,
                                width: image.naturalWidth,
                                height: image.naturalHeight,
                                ratio: image.naturalWidth / image.naturalHeight,
                            })
                        };
                        image.src = reader.result;
                    }
                }
                $event.preventDefault();
            },
            onAdd: function (event) {
                let image = new Image();
                console.log(this.droppedFiles[event.newIndex].file);
                image.onload = () => {
                    this.$eventHub.$emit('partymeister-slides:image-dropped', {
                        src: this.droppedFiles[event.newIndex].file.url,
                        width: image.naturalWidth,
                        height: image.naturalHeight,
                        ratio: image.naturalWidth / image.naturalHeight,
                    });
                };
                image.src = this.droppedFiles[event.newIndex].file.url;
            },
            isImage: function (file) {
                if (file.file.mime_type == 'image/png' || file.file.mime_type == 'image/jpg' || file.file.mime_type == 'image/jpeg' || file.file.mime_type == 'video/x-m4v') {
                    return true;
                }
                return false;
            },
        },
        mounted: function () {
            this.interval = setInterval(() => {
                // Dragging has stopped
                if (this.dragInProgress !== false && this.dragInProgress < Date.now() - 1000) {
                    this.dragInProgress = false;
                    this.onDragLeave();
                }
            }, 500);
            document.body.addEventListener('dragover', this.onDragOver, false);
            document.body.addEventListener('drop', this.onDrop, false)
        },
        beforeDestroy() {
            this.$eventHub.$off();
            document.body.removeEventListener('dragover', this.onDragOver, false);
            document.body.removeEventListener('drop', this.onDrop, false);

            clearInterval(this.interval);
        },
    }
</script>
<style lang="scss">
    #dropzone div {
        display: flex;
        text-align: center;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 50px;
        color: #333;
        width: 100%;
        height: 100%;
        position: absolute;
    }
    #dropzone {
        display: flex;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: white;
        opacity: 0.9;
        z-index: 13000;
    }
</style>
