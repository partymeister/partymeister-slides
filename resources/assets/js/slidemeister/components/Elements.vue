<template>
    <div>
        <div v-if="showOverlay" class="slidemeister-overlay"></div>
        <div v-for="(element, index) in elements" :key="index+'0'" class="snappable-shadow"
             :class="'shadow-'+element.name"></div>
        <Moveable
                :ref="`${element.name}`"
                class="moveable"
                :class="element.name"
                v-bind="element.moveable"
                v-model="element[index]"
                :container="$refs.smcontainer"
                @drag="handleDrag"
                @dragStart="addStepToUndoStack"
                @resize="handleResize"
                @resizeStart="addStepToUndoStack"
                @rotate="handleRotate"
                @rotateStart="addStepToUndoStack"
                @warp="handleWarp"
                @warpStart="addStepToUndoStack"
                @click.native="setActiveElement($event, index)"
                @dblclick.native="focusEditor($event)"
                @mouseover.native="mouseOver($event, index)"
                @mouseout.native="mouseOut($event)"
                v-for="(element, index) in elements"
                :key="index"
        >
            <medium-editor :text="element.properties.content" :options='options'
                           v-on:edit='processEditOperation' custom-tag='div'>

            </medium-editor>
        </Moveable>
    </div>
</template>
<script>
    import Moveable from 'vue-moveable';
    import editor from 'vue2-medium-editor';
    import resizeText from '../mixins/resizeText';
    import mediumEditor from '../mixins/mediumEditor';
    import elementProperties from "../mixins/elementProperties";
    import vueUndoRedo from 'vue-undo-redo-stack';
    import renderTimetable from "../mixins/renderTimetable";
    import renderCompetition from "../mixins/renderCompetition";
    import renderPrizegiving from "../mixins/renderPrizegiving";
    import renderHelper from "../mixins/renderHelper";
    import Vue from 'vue';
    import elementNameHelper from "../mixins/elementNameHelper";

    let slugify = require('slugify');

    export default {
        name: 'partymeister-slides-elements',
        props: ['name', 'readonly', 'elementData', 'loadTemplate'],
        components: {
            Moveable,
            'medium-editor': editor
        },
        mixins: [
            resizeText,
            mediumEditor,
            elementProperties,
            vueUndoRedo,
            renderHelper,
            renderTimetable,
            renderCompetition,
            renderPrizegiving,
            elementNameHelper,
        ],
        data: () => ({
            templateId: '',
            templateType: 'basic',
            elements: {},
            elementOrder: [],
            key: undefined,
            options: {placeholder: {text: ''}},
            activeElement: undefined,
            showOverlay: false,
        }),
        computed: {
            checkpointData() {
                return {
                    elements: JSON.parse(JSON.stringify(this.elements)),
                    elementOrder: JSON.parse(JSON.stringify(this.elementOrder))
                }; // 2. Declare what data to stack when this.checkpoint() is called
            },
        },
        mounted() {
            window.addEventListener('keydown', (event) => {
                if (event.keyCode === 90 && (event.ctrlKey || event.metaKey) && event.shiftKey) {
                    this.redo();
                    event.preventDefault();
                } else if (event.keyCode === 90 && (event.ctrlKey || event.metaKey)) {
                    this.undo();
                    event.preventDefault();
                }
            });

            // this.$eventHub.$on('partymeister-slides:file-loaded', (data) => {
            //     this.$eventHub.$emit('partymeister-slides:load-definitions', {
            //         name: "template-editor",
            //         elements: JSON.parse(data)
            //     });
            // });

            this.$eventHub.$on('partymeister-slides:load-definitions', (data) => {
                if (data.name !== this.name) {
                    return;
                }

                Vue.set(this, 'elements', data.elements.elements);
                Vue.set(this, 'templateId', data.elements.id);
                Vue.set(this, 'templateType', data.elements.type);
                Vue.set(this, 'elementOrder', []);


                // Hide control boxes if we're in read only mode
                if (this.readonly) {
                    setTimeout(() => {
                        let controlBoxes = document.querySelectorAll(".moveable-control-box");

                        controlBoxes.forEach((box) => {
                            box.style.display = 'none';
                            box.classList.add('hidden');
                        });

                        let editors = document.querySelectorAll("#" + this.name + '.medium-editor-element');
                        editors.forEach((editor) => {
                            editor.setAttribute('contentEditable', 'false');
                        });

                    }, 100);
                }

                if (!this.readonly) {
                    let activeElement = undefined;
                    Object.entries(this.elements).forEach(([key, element]) => {
                        activeElement = key;
                        this.updateElementOrder(element);
                    });
                    this.$forceNextTick(() => {
                        this.setActiveElement({}, activeElement);
                        this.updateAllElementProperties();
                        this.updateSnappableGuidelines();
                        this.emitAllElements();
                    });
                } else {
                    this.updateAllElementProperties();
                    if (data.type) {
                        switch (data.type) {
                            case 'timetable':
                                this.renderTimetable(data.replacements);
                                break;
                            case 'event-support':
                                this.renderEventSupport(data.replacements);
                                break;
                            case 'competition-support':
                                this.renderCompetitionSupport(data.replacements);
                                break;
                            case 'competition-entry':
                                this.renderCompetitionEntry(data.replacements);
                                break;
                            case 'competition-participants':
                                this.renderCompetitionParticipants(data.replacements);
                                break;
                            case 'prizegiving-support':
                                this.renderPrizegivingSupport(data.replacements);
                                break;
                            case 'prizegiving-slide':
                                this.renderPrizegivingSlide(data.replacements);
                                break;
                            case 'prizegiving-winners':
                                this.renderPrizegivingWinners(data.replacements);
                                break;
                        }
                    }
                }

            });

            this.$eventHub.$on('partymeister-slides:download-definitions', (name) => {
                if (name === this.name) {
                    if (this.templateId === '') {
                        this.templateId = this.createElementName();
                    }
                    let exportData = {
                        id: this.templateId,
                        elements: this.elements,
                    };

                    let data = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(exportData));
                    let button = document.querySelector('#download-definitions');

                    button.setAttribute("href", data);

                    button.setAttribute("download", "slide-template-definitions_" + slugify(this.templateId) + ".json");
                    button.click();
                }
            });

            this.$eventHub.$on('partymeister-slides:request-definitions', (name) => {
                // console.log("request definitions for " + name);
                if (name !== this.name) {
                    return;
                }
                let meta = [];
                Object.entries(this.elements).forEach(([key, element]) => {
                    if (element.properties.prizegivingbarCoordinates) {
                        meta.push(element.properties.prizegivingbarCoordinates);
                    }
                });

                if (this.templateId === '') {
                    this.templateId = this.createElementName();
                }

                let definitions = JSON.stringify({
                    id: this.templateId,
                    type: this.templateType,
                    elements: this.elements,
                });

                this.$eventHub.$emit('partymeister-slides:receive-definitions', {
                    definitions: definitions,
                    definitions_as_form_data: this.addslashes(definitions),
                    meta: JSON.stringify(meta),
                    name: this.name
                });
            });

            /**
             * Switch mode (resizable / warpable)
             */
            this.$eventHub.$on('partymeister-slides:switch-mode', (data) => {
                if (data.mode === 'resizable') {
                    this.elements[data.name].properties.resizable = true;
                    this.elements[data.name].properties.warpable = false;
                } else {
                    this.elements[data.name].properties.resizable = false;
                    this.elements[data.name].properties.warpable = true;
                }
                this.elements[data.name].moveable.resizable = this.elements[data.name].properties.resizable;
                this.elements[data.name].moveable.warpable = this.elements[data.name].properties.warpable;
                this.setActiveElement({}, data.name);
            });

            /**
             * Update element order
             */
            this.$eventHub.$on('partymeister-slides:new-element-order', (order) => {
                this.addStepToUndoStack();
                this.elementOrder = JSON.parse(JSON.stringify(order));
                this.updateZIndexByElementOrder();
                this.emitAllElements();
            });

            this.$eventHub.$on('partymeister-slides:add-step-to-undo-stack', (data) => {
                // Check if property has changed since the last save
                if (data.property && this.undoStack.length > 0) {
                    if (this.undoStack[this.undoStack.length - 1].elements && this.undoStack[this.undoStack.length - 1].elements[data.element]) {
                        if (this.undoStack[this.undoStack.length - 1].elements[data.element].properties[data.property] !== data.value) {
                            this.addStepToUndoStack();
                        }
                    }
                } else {
                    this.addStepToUndoStack();
                }
            });

            /**
             * Update element properties
             */
            this.$eventHub.$on('partymeister-slides:update-element-properties', (data) => {
                if (data.name) {
                    this.elements[data.name].properties = data.properties;
                    this.elements[data.name].moveable.snappable = data.properties.snapping;
                    this.updateElementProperties(this.elements[data.name]);
                    this.setLockedProperty(this.elements[data.name]);
                }
            });

            /**
             * Delete element
             */
            this.$eventHub.$on('partymeister-slides:delete-element', (name) => {
                this.addStepToUndoStack();
                Vue.delete(this.elements, name);
                this.updateSnappableGuidelines();

                this.deleteElementFromElementOrder(name);
                this.updateAllElementProperties();
                if (this.elementOrder.length > 0) {
                    this.setActiveElement({}, this.elementOrder[0].name);
                } else {
                    this.setActiveElement({}, null);
                }
            });

            /**
             * Clone existing element
             */
            this.$eventHub.$on('partymeister-slides:clone-element', (data) => {
                this.addStepToUndoStack();
                let element = this.cloneElement(data, this.createElementName());

                Vue.set(this.elements, element.name, element);
                this.$forceNextTick(() => {
                    this.updateSnappableGuidelines();
                    this.updateElementOrder(element);
                    this.updateElementProperties(element);
                    this.updateAndSetActive(element, element.name);
                });
            });

            /**
             * Add new element
             */
            this.$eventHub.$on('partymeister-slides:add-element', (name) => {
                this.addElement(name);
            });
            this.$eventHub.$on('partymeister-slides:image-dropped', (data) => {
                let elementName = this.addElement('element_' + this.createElementName(), data.src);
                this.adjustImageElement(this.elements[elementName], data);
            });
            this.$eventHub.$on('partymeister-slides:image-dropped-from-dataurl', (data) => {
                let elementName = this.addElement('element_' + this.createElementName(), '', data.dataUrl);
                this.adjustImageElement(this.elements[elementName], data);
            });

            this.$eventHub.$on('partymeister-slides:update-id', (id) => {
                this.templateId = id;
                this.emitAllElements();
            });

            this.$eventHub.$on('partymeister-slides:update-type', (type) => {
                this.templateType = type;
                this.emitAllElements();
            });

            if (this.readonly) {
                this.showOverlay = true;
            }

            if (this.elementData) {
                this.$eventHub.$emit('partymeister-slides:load-definitions', {
                    name: this.name,
                    elements: JSON.parse(JSON.stringify(this.elementData))
                })
                this.showOverlay = true;
            }

            if (this.loadTemplate !== undefined) {
                let templates = localStorage.getItem('templates');
                templates = JSON.parse(templates);
                if (templates[this.loadTemplate] !== undefined) {
                    this.$eventHub.$emit('partymeister-slides:load-definitions', {
                        name: this.name,
                        elements: JSON.parse(JSON.stringify(templates[this.loadTemplate]))
                    })
                }
            }
        },
        beforeDestroy() {
            this.$eventHub.$off();
        },
        methods: {
            addslashes(string) {
                return string.replace(/\\/g, '\\\\').replace(/\u0008/g, '\\b').replace(/\t/g, '\\t').replace(/\n/g, '\\n').replace(/\f/g, '\\f').replace(/\r/g, '\\r').replace(/'/g, '\\\'').replace(/"/g, '\\"');
            },
            addElement(name, image, dataUrl) {
                this.addStepToUndoStack();
                let element = this.createEmptyElement(name, image, dataUrl);
                Vue.set(this.elements, name, element);
                setTimeout(() => {
                    this.updateSnappableGuidelines();
                    this.updateElementOrder(element);
                    this.updateAndSetActive(element, element.name);
                });
                return element.name;
            },
            restoreCheckpoint(checkpointData) {
                if (checkpointData) {
                    Vue.set(this, 'elements', checkpointData.elements);
                    Vue.set(this, 'elementOrder', checkpointData.elementOrder);
                    // this.elements = checkpointData.elements;
                    // this.elementOrder = checkpointData.elementOrder;
                    setTimeout(() => {
                        if (!this.elements[this.activeElement.name]) {
                            this.setActiveElement({}, null);
                        } else {
                            this.setActiveElement({}, this.activeElement.name);
                        }
                        this.updateAllElementProperties();
                        this.updateSnappableGuidelines();
                        this.emitAllElements();
                    }, 100);
                }
            },
            emitAllElements() {
                this.$eventHub.$emit('partymeister-slides:all-elements', {
                    elements: this.elements,
                    order: this.elementOrder,
                    id: this.templateId,
                    type: this.templateType,
                });
            },
            deleteElementFromElementOrder(name) {
                const orderIndex = this.elementOrder.findIndex(element => element.name === name);
                this.elementOrder.splice(orderIndex, 1);
            },
            updateElementOrder(element) {
                this.elementOrder.push({name: element.name, zIndex: element.properties.zIndex});
                this.elementOrder.sort((a, b) => (a.zIndex < b.zIndex) ? 1 : -1);
            },
            updateAndSetActive(element, name) {
                this.$forceNextTick(() => {
                    this.updateElementProperties(element);
                    this.setActiveElement({}, name);
                    this.emitAllElements();
                    setTimeout(() => {
                        document.querySelector('#' + this.name + ' .' + name).style.visibility = '';
                    }, 50);
                });
            },
            setActiveElement($event, index) {
                if (index === null) {
                    this.$eventHub.$emit('partymeister-slides:active-element', {
                        elements: undefined,
                        activeElement: undefined
                    });
                    return;
                }
                this.activeElement = this.elements[index];

                this.$eventHub.$emit('partymeister-slides:active-element', {
                    elements: this.elements,
                    activeElement: this.elements[index],
                });

                Object.entries(this.elements).forEach(([key, element]) => {
                    if (key !== index || this.readonly) {
                        element.moveable.resizable = false;
                        element.moveable.warpable = false;
                        element.moveable.draggable = false;
                        element.moveable.rotatable = false;
                    } else if (key === index && !element.properties.locked) {
                        element.moveable.resizable = element.properties.resizable;
                        element.moveable.warpable = element.properties.warpable;
                        element.moveable.draggable = true;
                        element.moveable.rotatable = true;
                    }
                });
            },
            mouseOver($event, index) {
                this.$eventHub.$emit('partymeister-slides:mouseover', this.elements[index]);
            },
            mouseOut($event) {
                this.$eventHub.$emit('partymeister-slides:mouseout');
            },
            addStepToUndoStack() {
                this.checkpoint();
            },
            updateCoordinates(target) {
                let element = this.elements[target.classList[1]];
                element.properties.coordinates = {
                    transform: target.style.transform,
                    width: parseInt(target.style.width.replace('px', '')),
                    height: parseInt(target.style.height.replace('px', ''))
                };
                this.updateElementProperties(element);
                this.updateSnappableGuidelines();
                this.emitAllElements();
            },
            handleDrag({target, transform}) {
                // console.log('onDrag left, top', transform);
                target.style.transform = transform;
                this.updateCoordinates(target);
            },
            handleResize({target, width, height, delta}) {
                // console.log('onResize', width, height);
                delta[0] && (target.style.width = `${width}px`);
                delta[1] && (target.style.height = `${height}px`);
                this.resizeText(target, width, height);
                this.updateCoordinates(target);
            },
            handleRotate({target, dist, transform}) {
                // console.log('onRotate', dist);
                target.style.transform = transform;
                this.updateCoordinates(target);
            },
            handleWarp({target, transform}) {
                // console.log('onWarp', transform);
                target.style.transform = transform;
                this.updateCoordinates(target);
            },
            adjustImageElement(element, data) {
                // adjust ratio
                element.properties.coordinates.width = 200 * data.ratio;
                element.properties.coordinates.heightwidth = 200 * data.ratio;
                element.properties.content = '';
                this.updateElementProperties(element);
            }
        }
    }
</script>
<style>
    .slidemeister-overlay {
        clip-path: inset(0);
        background-color: transparent;
        border: 1px solid black;
        position: absolute;
        width: 960px;
        height: 540px;
        z-index: 10000;

        background-size: cover;
        background-repeat: no-repeat;
        background-position: center center;
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

    .medium-editor-element:focus {
        outline: none !important;
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
</style>
