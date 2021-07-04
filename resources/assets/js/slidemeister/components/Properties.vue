<template>
    <div>
        <h6>Properties</h6>
        <div v-if="activeElement != undefined" id="slidemeister-properties">
            <div :class="{hidden: simple}" class="input-group">
                <label class="col-form-label col-form-label-sm" for="prettyname">prettyname</label>
                <input @focus="addStepToUndoStack('prettyname')" @blur="updateProperties"
                       class="form-control form-control-sm" id="prettyname"
                       type="text" name="prettyname"
                       v-model="activeElement.properties.prettyname">
            </div>
            <div :class="{hidden: simple}" class="input-group">
                <label class="col-form-label col-form-label-sm" for="placeholder">placeholder</label>
                <input @focus="addStepToUndoStack('placeholder')" @blur="updateProperties"
                       class="form-control form-control-sm" id="placeholder" type="text" name="placeholder"
                       v-model="activeElement.properties.placeholder">
            </div>
            <div class="input-group">
                <label class="col-form-label col-form-label-sm" for="fontFamily">fontFamily</label>
                <select @focus="addStepToUndoStack('fontFamily')" @change="updateProperties"
                        v-model="activeElement.properties.fontFamily" class="form-control form-control-sm"
                        id="fontFamily" name="fontFamily">
                    <option v-for="font in fonts" :value="font">{{font}}</option>
                </select>
            </div>
            <div class="input-group">
                <label class="col-form-label col-form-label-sm" for="webfontloader">webfontloader</label>
                <input @blur="loadFontEvent"
                       class="form-control form-control-sm" id="webfontloader" type="text" name="webfontloader">
            </div>
            <div :class="{hidden: simple}" class="input-group">
                <label class="col-form-label col-form-label-sm" for="size">size</label>
                <select @focus="addStepToUndoStack('size')" @change="updateProperties"
                        class="form-control form-control-sm" id="size" name="size"
                        v-model="activeElement.properties.size">
                    <option value="individual">individual</option>
                    <option value="fill">fill</option>
                </select>
            </div>
            <div class="input-group">
                <label class="col-form-label col-form-label-sm" for="fontSize">fontSize</label>
                <input @focus="addStepToUndoStack('fontSize')" @blur="updateProperties"
                       class="form-control form-control-sm" id="fontSize" type="text" name="fontSize"
                       v-model="activeElement.properties.fontSize">
            </div>
            <div class="input-group">
                <label class="col-form-label col-form-label-sm" for="fontWeight">fontWeight</label>
                <select @focus="addStepToUndoStack('fontWeight')" @change="updateProperties"
                        class="form-control form-control-sm" id="fontWeight" name="fontWeight"
                        v-model="activeElement.properties.fontWeight">
                    <option value="normal">regular</option>
                    <option value="bold">bold</option>
                </select>
            </div>
            <div class="input-group">
                <label class="col-form-label col-form-label-sm" for="fontStyle">fontStyle</label>
                <select @focus="addStepToUndoStack('fontStyle')" @change="updateProperties"
                        class="form-control form-control-sm" id="fontStyle" name="fontStyle"
                        v-model="activeElement.properties.fontStyle">
                    <option value="normal">normal</option>
                    <option value="italic">italic</option>
                </select>
            </div>
            <div class="input-group">
                <label class="col-form-label col-form-label-sm" for="textAlign">textAlign</label>
                <select @focus="addStepToUndoStack('textAlign')" @change="updateProperties"
                        class="form-control form-control-sm" id="textAlign" name="textAlign"
                        v-model="activeElement.properties.textAlign">
                    <option value="left">left</option>
                    <option value="center">center</option>
                    <option value="right">right</option>
                </select>
            </div>
            <div class="input-group">
                <label class="col-form-label col-form-label-sm" for="verticalAlign">verticalAlign</label>
                <select @focus="addStepToUndoStack('verticalAlign')" @change="updateProperties"
                        class="form-control form-control-sm" id="verticalAlign" name="verticalAlign"
                        v-model="activeElement.properties.verticalAlign">
                    <option value="flex-start">top</option>
                    <option value="center">center</option>
                    <option value="flex-end">bottom</option>
                </select>
            </div>
            <colorpicker label="color" :color="activeElement.properties.color"
                         v-model="activeElement.properties.color"/>
            <div :class="{hidden: simple}">
                <colorpicker label="background" :color="activeElement.properties.backgroundColor"
                             v-model="activeElement.properties.backgroundColor"/>
            </div>
            <div :class="{hidden: simple}" class="input-group">
                <label class="col-form-label col-form-label-sm" for="locked">locked</label>
                <input @focus="addStepToUndoStack('locked')" @blur="updateProperties" type="checkbox"
                       class="form-check-input" id="locked" name="locked"
                       v-model="activeElement.properties.locked">
            </div>
            <div :class="{hidden: simple}" class="input-group">
                <label class="col-form-label col-form-label-sm" for="visibility">visibility</label>
                <select @focus="addStepToUndoStack('visibility')" @change="updateProperties"
                        class="form-control form-control-sm" id="visibility" name="visibility"
                        v-model="activeElement.properties.visibility">
                    <option value="render">render</option>
                    <option value="preview">preview</option>
                </select>
            </div>
            <div :class="{hidden: simple}" class="input-group">
                <label class="col-form-label col-form-label-sm" for="lineHeight">lineHeight</label>
                <input @focus="addStepToUndoStack('lineHeight')" @blur="updateProperties"
                       class="form-control form-control-sm" id="lineHeight" type="text" name="lineHeight"
                       v-model="activeElement.properties.lineHeight">
            </div>
            <div :class="{hidden: simple}" class="input-group">
                <label class="col-form-label col-form-label-sm" for="textShadow">textShadow</label>
                <input @focus="addStepToUndoStack('textShadow')" @blur="updateProperties"
                       class="form-control form-control-sm" id="textShadow" type="text" name="textShadow"
                       v-model="activeElement.properties.textShadow">
            </div>
            <div :class="{hidden: simple}" class="input-group">
                <label class="col-form-label col-form-label-sm" for="textTransform">textTransform</label>
                <input @focus="addStepToUndoStack('textTransform')" @blur="updateProperties"
                       class="form-control form-control-sm" id="textTransform" type="text" name="textTransform"
                       v-model="activeElement.properties.textTransform">
            </div>
            <div :class="{hidden: simple}" class="input-group">
                <label class="col-form-label col-form-label-sm" for="editable">editable</label>
                <input @focus="addStepToUndoStack('editable')" @blur="updateProperties" type="checkbox"
                       class="form-check-input" id="editable" name="editable"
                       v-model="activeElement.properties.editable">
            </div>
            <div :class="{hidden: simple}" class="input-group">
                <label class="col-form-label col-form-label-sm" for="opacity">opacity</label>
                <input @focus="addStepToUndoStack('opacity')" @blur="updateProperties"
                       class="form-control form-control-sm" id="opacity" type="text" name="opacity"
                       v-model="activeElement.properties.opacity">
            </div>
            <div :class="{hidden: simple}" class="input-group">
                <label class="col-form-label col-form-label-sm" for="snapping">snapping</label>
                <input @focus="addStepToUndoStack('snapping')" @blur="updateProperties" type="checkbox"
                       class="form-check-input" id="snapping" name="snapping"
                       v-model="activeElement.properties.snapping">
            </div>
            <div :class="{hidden: simple}">
                <div class="btn-group btn-block" role="group" aria-label="switch-mode">
                    <button :class="{'btn-primary': activeElement.moveable.resizable, 'btn-secondary': activeElement.moveable.warpable}"
                            type="button" @click="changeMode('resizable')" class="btn btn-sm">Resize
                    </button>
                    <button :class="{'btn-primary': activeElement.moveable.warpable, 'btn-secondary': activeElement.moveable.resizable}"
                            type="button" @click="changeMode('warpable')" class="btn btn-sm">Warp
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>

    import colorpicker from '../components/ColorPicker';
    import webFontLoader from "../mixins/webFontLoader";

    export default {
        name: 'partymeister-slides-properties',
        props: ['simple'],
        mixins: [
            webFontLoader
        ],
        components: {
            colorpicker,
        },
        data: () => ({
            elements: [],
            activeElement: undefined,
            activeElementIndex: null,
            mouseIsUp: true,
            fonts: ['Arial', 'Verdana', '\'Exo 2\'', '\'Booterfz\'', '\'Blackpearl\'']
        }),
        mounted() {
            this.$eventHub.$on('partymeister-slides:load-font', (font) => {
                if (!this.fonts.includes(font)) {
                    console.log("Request loading font " + font);
                    this.fonts.push(font);
                    this.loadFont(font);
                }
            });
            this.$eventHub.$on('partymeister-slides:active-element', (data) => {
                this.activeElement = data.activeElement;
            });
            this.$eventHub.$on('color-picker:value-change', (value) => {
                this.updateProperties();
            });
            this.$eventHub.$on('partymeister-slides:open-color-picker', () => {
                this.addStepToUndoStack();
            });
        },
        methods: {
            changeMode(mode) {
                this.$eventHub.$emit('partymeister-slides:switch-mode', {name: this.activeElement.name, mode: mode});
            },
            addStepToUndoStack(property) {
                if (property && this.activeElement) {
                    this.$eventHub.$emit('partymeister-slides:add-step-to-undo-stack', {
                        property: property,
                        value: this.activeElement.properties[property],
                        element: this.activeElement.name,
                    });
                } else {
                    this.$eventHub.$emit('partymeister-slides:add-step-to-undo-stack', {
                        property: undefined,
                        value: undefined,
                        element: undefined
                    });
                }
            },
            updateProperties() {
                this.$eventHub.$emit('partymeister-slides:update-element-properties', {
                    properties: this.activeElement.properties,
                    name: this.activeElement.name
                });
            },
        }
    }
</script>
<style scoped>
    .hidden {
        display: none;
    }

    #slidemeister-properties div {
        justify-content: space-between;
    }

    .form-check-input {
        position: relative;
        margin-top: 0.5rem;
    }
</style>
