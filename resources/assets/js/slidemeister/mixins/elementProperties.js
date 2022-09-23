export default {
    methods: {
        cloneElement(data) {
            let element = JSON.parse(JSON.stringify(this.elements[data.name]));
            element.name = data.uniqid;
            element.properties.zIndex = 2000 + this.elementOrder.length + 1;
            return element;
        },
        createEmptyElement(name, image, dataUrl) {
            return {
                name: name,
                moveable: {
                    draggable: false,
                    warpable: false,
                    throttleDrag: 0,
                    resizable: false,
                    throttleResize: 1,
                    keepRatio: false,
                    snappable: true,
                    snapThreshold: 10,
                    scalable: false,
                    throttleScale: 0,
                    rotatable: false,
                    throttleRotate: 0,
                    pinchable: false, // ["draggable", "resizable", "scalable", "rotatable"]
                    origin: false,
                    elementGuidelines: [],
                },
                properties: {
                    image: image,
                    dataUrl: dataUrl,
                    resizable: true,
                    warpable: false,
                    prettyname: '',
                    placeholder: '',
                    locked: false,
                    fontFamily: 'Arial',
                    fontSize: 30,
                    fontKerning: 'auto',
                    calculatedFontSize: 30,
                    fontWeight: 'normal',
                    fontStyle: 'normal',
                    textAlign: 'left',
                    horizontalAlign: 'flex-start',
                    verticalAlign: 'flex-start',
                    color: '#000000',
                    backgroundColor: 'transparent',
                    opacity: 1.0,
                    visibility: 'render',
                    editable: true,
                    size: 'individual',
                    snapping: true,
                    zIndex: 2000 + this.elementOrder.length,
                    content: 'Enter text',
                    textShadow: 'none',
                    textTransform: 'none',
                    lineHeight: 'normal',
                    coordinates: {
                        transform: 'matrix(1, 0, 0, 1, 0, 0) translate(0px 0px)',
                        width: 300,
                        height: 200,
                    },
                    prizegivingbarCoordinates: {
                        x1: 0,
                        x2: 0,
                        y1: 0,
                        y2: 0,
                    }
                }
            };
        },
        updateSnappableGuidelines() {

            for (let [key, element] of Object.entries(this.elements)) {
                let elementGuidelines = [
                    document.querySelector("#slidemeister"),
                ];
                for (let [k, e] of Object.entries(this.elements)) {
                    if (e.name !== element.name) {
                        const shadowSelector = document.querySelector('.shadow-' + e.name);
                        if (shadowSelector !== null) {
                            elementGuidelines.push(shadowSelector);
                        }
                    }
                }
                if (element.properties.snapping) {
                    element.moveable.elementGuidelines = elementGuidelines;
                } else {
                    element.moveable.elementGuidelines = [];
                }
            }
        },
        updateZIndexByElementOrder() {
            for (const [index, orderElement] of this.elementOrder.entries()) {
                this.elementOrder[index].zIndex = 2000 - index;
                this.elements[orderElement.name].properties.zIndex = 2000 - index;
                this.updateAllElementProperties();
            }
        }
        ,
        setLockedProperty(element) {
            if (element.properties.locked) {
                element.moveable.resizable = false;
                element.moveable.draggable = false;
                element.moveable.rotatable = false;
                element.moveable.warpable = false;
            } else {
                element.moveable.resizable = element.properties.resizable;
                element.moveable.warpable = element.properties.warpable;
                element.moveable.draggable = true;
                element.moveable.rotatable = true;
            }
        }
        ,
        updateElementProperties(element) {
            this.$forceNextTick(() => {
                const target = document.querySelector('#' + this.name + ' .' + element.name);
                if (target === null) {
                    return;
                }
                let content = target.querySelector('div');

                let shadowElement = document.querySelector('#' + this.name + ' .shadow-' + element.name);
                // Weirdly enough, undo doesn't work if the transform is bascially set to zero. so we need to set it to empty and THEN set the desired value
                shadowElement.style.transform = '';
                shadowElement.style.transform = element.properties.coordinates.transform;
                shadowElement.style.width = element.properties.coordinates.width + 'px';
                shadowElement.style.height = element.properties.coordinates.height + 'px';

                // Weirdly enough, undo doesn't work if the transform is bascially set to zero. so we need to set it to empty and THEN set the desired value
                target.style.transform = '';
                target.style.transform = element.properties.coordinates.transform;
                target.style.width = element.properties.coordinates.width + 'px';
                target.style.height = element.properties.coordinates.height + 'px';

                content.style.fontFamily = element.properties.fontFamily;
                content.style.fontSize = element.properties.fontSize + 'px';
                content.style.fontKerning = element.properties.fontKerning;
                content.style.fontWeight = element.properties.fontWeight;
                content.dataset.fontSize = element.properties.fontSize;
                content.style.fontStyle = element.properties.fontStyle;
                content.style.textAlign = element.properties.textAlign;
                target.style.alignItems = element.properties.verticalAlign;
                content.style.color = element.properties.color;
                target.style.backgroundColor = element.properties.backgroundColor;
                target.style.opacity = element.properties.opacity;
                target.style.zIndex = element.properties.zIndex;
                target.style.textShadow = element.properties.textShadow;
                target.style.lineHeight = element.properties.lineHeight;
                target.style.textTransform = element.properties.textTransform;
                target.dataset.partymeisterSlidesVisibility = element.properties.visibility;

                content.innerHTML = element.properties.content;
                target.style.width = element.properties.coordinates.width + 'px';
                target.style.height = element.properties.coordinates.height + 'px';
                if (element.properties.image) {
                    // replace url with absolute path to get rid of problems with changing urls
                    element.properties.image = this.replaceUrlWithPath(element.properties.image);
                    target.style.backgroundImage = 'url(' + element.properties.image + ')';
                }
                if (element.properties.dataUrl) {
                    target.style.backgroundImage = 'url(' + element.properties.dataUrl + ')';
                }

                if (element.properties.size === 'fill') {
                    this.handleRotate({
                        target: target,
                        dist: 0,
                        transform: 'matrix(1, 0, 0, 1, 0, 0) translate(2px 0px)'
                    });
                    target.style.transform = '';
                    target.style.top = '0px';
                    target.style.left = '0px';
                    this.handleDrag({target: target, transform: 'matrix(1,0,0,1,0,0) translate(0px 0px)'});
                    this.handleResize({target: target, width: 960, height: 540, delta: [1, 1]});
                }
                if (this.$refs[element.name][0] !== null && this.$refs[element.name][0] !== undefined) {
                    this.$refs[element.name][0].updateRect();
                }
                this.resizeText(target);
                element.properties.size = 'individual';
            });
        }
        ,
        replaceUrlWithPath(url) {
            return url.substring(url.search('/media'));
        },
        updateAllElementProperties() {
            Object.entries(this.elements).forEach(([key, element]) => {
                this.updateElementProperties(element);
            });
        }
        ,
    }
}
;
