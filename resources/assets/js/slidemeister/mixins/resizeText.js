export default {
    methods: {
        resizeText(target, width, height) {
            setTimeout(() => {
                if (this.readonly) {
                    this.$forceNextTick(() => {
                        this.doResize(target, width, height);
                    });
                } else {
                    this.doResize(target, width, height);
                }
            }, 250);
        },
        doResize(target, width, height) {
            if (!width) {
                width = target.offsetWidth;
            }
            if (!height) {
                height = target.offsetHeight;
            }

            let textElement = target.querySelector('div');

            while ((textElement.offsetWidth < width && textElement.offsetHeight < height) && parseInt(textElement.style.fontSize.replace('px', '')) <= parseInt(textElement.dataset.fontSize)) {
                textElement.style.fontSize = (parseInt(textElement.style.fontSize.replace('px', '')) + 1) + 'px';
                target.style.fontSize = textElement.style.fontSize;
            }

            while ((textElement.offsetWidth > width || textElement.offsetHeight > height) && parseInt(textElement.style.fontSize.replace('px', '')) > 5) {
                textElement.style.fontSize = (parseInt(textElement.style.fontSize.replace('px', '')) - 1) + 'px';
                target.style.fontSize = textElement.style.fontSize;
            }

            // Separate adjustment because linux renders fonts differently which might cause problems
            if ((height - textElement.offsetHeight) < 10 ) {
                textElement.style.fontSize = (parseInt(textElement.style.fontSize.replace('px', '')) - 1) + 'px';
                target.style.fontSize = textElement.style.fontSize;
            }

            let element = this.elements[target.classList[1]];
            element.properties.calculatedFontSize = textElement.style.fontSize;
        }
    }
};
