(function () {
    Math.clamp = function (a, b, c) {
        return Math.max(b, Math.min(c, a));
    }
})();

export default {
    methods: {
        deleteBars() {
            var elem = document.querySelector('#slidemeister-bar-wrapper');
            if (elem === null) {
                return;
            }
            elem.parentNode.removeChild(elem);
        },
        clearSiegmeisterBars() {
            if (this.currentBackground !== 'siegmeister_winners' && this.currentBackground !== 'siegmeister_bars') {
                this.deleteBars();
            }
        },
        renderPrizegivingBars() {
            console.log("Siegmeister bars triggered");
            // remove potentially existing bar elements and remove them from the dom
            this.deleteBars();

            // Create bars element and attach it to the dom
            let bars = document.createElement("div");
            bars.className = 'slidemeister-bar-wrapper';
            bars.id = 'slidemeister-bar-wrapper';

            let metadata = JSON.parse(this.items[this.currentItem].metadata);

            let zoom = document.querySelector('.slidemeister-instance').style.zoom;

            console.log(metadata);

            for (const [index, e] of metadata.entries()) {
                let bar = document.createElement("div");
                let left = Number((e.x1 * 960 * zoom).toFixed(2));
                let width = Number((e.x2 * 960 * zoom - left).toFixed(2));
                let top = Number((e.y1 * 540 * zoom).toFixed(2));
                let height = Number((e.y2 * 540 * zoom - top).toFixed(2));
                bar.id = 'bar-' + index;
                bar.style.left = left + 'px';
                bar.style.top = top + 'px';
                bar.style.width = 0;
                bar.style.height = height + 'px';
                bar.className = 'slidemeister-bars active';
                bar.style.backgroundColor = this.prizegivingBarColor;
                bars.appendChild(bar);
                metadata[index].id = 'bar-' + index;
            }
            document.body.appendChild(bars);

            // Animate
            this.animatePrizegivingBars(metadata, 0);
        },
        animatePrizegivingBars(bars, frame) {
            if (frame === 240) {
                window.clearTimeout(barTimeout);

                bars.sort(function (a, b) {
                    return (a.x2 < b.x2) ? 1 : ((b.x2 < a.x2) ? -1 : 0);
                });

                let blinkingBars = [];
                let barValues = [];
                for (let [index, bar] of bars.entries()) {
                    if (index === 0) {
                        blinkingBars.push(index);
                        barValues.push(bar.x2);
                    } else {
                        if (this.items[this.currentItem].slide.name != 'Special: Bars') {
                            if (barValues.includes(bar.x2)) {
                                blinkingBars.push(index);
                                barValues.push(bar.x2);
                            } else if (barValues.length < 3) {
                                blinkingBars.push(index);
                                barValues.push(bar.x2);
                            }
                        }
                    }
                }

                for (const index of blinkingBars) {
                    if (bars[index] !== undefined) {
                        let bar = document.querySelector('#' + bars[index].id);
                        bar.style.backgroundColor = this.configuration.prizegiving_bar_blink_color;
                        bar.classList.add('blink');
                    }
                }

                this.seekToNextItem(true);
                return;
            }

            frame++;
            let barTimeout = setTimeout(() => {
                this.animatePrizegivingBars(bars, frame)
            }, 1000 / 60);
            let zoom = document.querySelector('.slidemeister-instance').style.zoom;

            for (const [index, e] of bars.entries()) {

                let time = frame / 240;

                let t = time + 0.25 * 0.5 * Math.sin(e.x2 * 2000) * (4 * (-time * time + time));
                let w = Math.clamp(t, 0, e.x2);

                let width = Number(((w - e.x1) * 960 * zoom).toFixed(2));
                if (width > e) {
                    width = e;
                }

                let bar = document.querySelector('#bar-' + index);
                bar.style.width = width + 'px';
            }
        },
    }
}
