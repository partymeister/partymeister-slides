import Vue from 'vue';
import * as Rematrix from 'rematrix';

export default {
    methods: {
        cloneElement(element, nameModifier, yOffset) {
            if (element.properties === undefined) {
                element = this.elements[element.name];
            }
            let clonedElement = JSON.parse(JSON.stringify(element));
            clonedElement.name = clonedElement.name + '_' + nameModifier;

            // Strip translate from css transform and add it to the new transform matrix
            let translatePosition = clonedElement.properties.coordinates.transform.indexOf('translate');
            let strippedTranslate = clonedElement.properties.coordinates.transform.substring(translatePosition + 10).replace(')', '').replace(' ', '').replace(/px/g, '');
            let savedCoordinates = strippedTranslate.split(',');
            let savedTranslate = Rematrix.translate(parseInt(savedCoordinates[0]), parseInt(savedCoordinates[1]))
            let savedTransform = Rematrix.fromString(clonedElement.properties.coordinates.transform);
            let translateY = Rematrix.translateY(yOffset);
            let newTransform = [savedTransform, savedTranslate, translateY].reduce(Rematrix.multiply);

            clonedElement.properties.coordinates.transform = Rematrix.toString(newTransform);

            Vue.set(this.elements, clonedElement.name, clonedElement);

            return this.elements[clonedElement.name];
        },
        replaceContentGlobal(name, value, update) {
            Object.entries(this.elements).forEach(([key, element]) => {
                this.replaceContent(element, name, value, update);
            });
        },
        replaceContent(element, name, value, update) {
            let content = element.properties.placeholder;
            if (Array.isArray(name)) {
                for (const [i, n] of name.entries()) {
                    console.log('REPLACE', n, value[i], content);
                    content = content.replace('<<' + n + '>>', value[i]);
                }
            } else {
                content = content.replace('<<' + name + '>>', value);
                console.log('ELSE', name, value, content);
            }

            if (content !== element.properties.placeholder) {
                element.properties.content = content;
                // Check if there is still stuff to replace (mostly for platform and options)
                if (element.properties.content.search('<<') > -1) {
                    element.properties.placeholder = content;
                }
                if (update) {
                    this.$forceNextTick(() => {
                        this.updateElementProperties(element);
                    });
                    // setTimeout(() => {
                    // }, 100);
                }
            }
        },
    }
};
