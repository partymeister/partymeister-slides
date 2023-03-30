import * as Rematrix from "rematrix";

export default {
    methods: {
        renderPrizegivingSupport(replacements) {
            Object.entries(this.elements).forEach(([key, element]) => {
                this.replaceContent(element, 'headline', replacements.headline);
                this.replaceContent(element, 'body', replacements.body);
            });
        },
        renderPrizegivingSlide(replacements) {
            this.renderPrizegivingSlideOrWinners(replacements, false);
        },
        renderPrizegivingWinners(replacements) {
            this.renderPrizegivingSlideOrWinners(replacements, true);
        },
        renderPrizegivingSlideOrWinners(replacements, includeRank) {
            // Replace headline
            this.replaceContentGlobal('headline', replacements.headline, true);
            let baseEntryElement, baseRemoteTypeElement, baseRankElement = false;

            Object.entries(this.elements).forEach(([key, element]) => {
                if (element.properties.prettyname === 'entry') {
                    baseEntryElement = element;
                }
                if (element.properties.prettyname === 'remote_type') {
                    baseRemoteTypeElement = element;
                }
                if (element.properties.prettyname === 'rank') {
                    baseRankElement = element;
                }
            });

            // Duplicate elements and replace placeholders
            replacements.rows.forEach((row, index) => {
                let entryElement;
                let remoteTypeElement;
                let rankElement;
                if (index === 0) {
                    entryElement = baseEntryElement;
                    rankElement = baseRankElement;
                    remoteTypeElement = baseRemoteTypeElement;
                } else {
                    entryElement = this.cloneElement(baseEntryElement, index, 50 * index);
                    remoteTypeElement = this.cloneElement(baseRemoteTypeElement, index, 50 * index);
                    rankElement = this.cloneElement(baseRankElement, index, 50 * index);
                }

                this.replaceContent(entryElement, ['title', 'author'], [row.title, row.author], true);
                this.replaceContent(remoteTypeElement, 'remote_type', row.remote_type, true);
                if (includeRank) {
                    this.replaceContent(rankElement, 'rank', '#' + row.rank, true);
                } else {
                    setTimeout(() => {
                        this.replaceContent(rankElement, 'rank', '', true);
                        setTimeout(() => {
                            this.calculatePrizegivingBarCoordinates(row, entryElement);
                        }, 500)
                    }, 500);
                }
            });
        },
        calculatePrizegivingBarCoordinates(row, entryElement) {
            this.$forceNextTick(() => {
                if (entryElement.properties.prettyname !== 'entry') {
                    return;
                }
                let target = document.querySelector('#' + this.name + ' .' + entryElement.name);
                let width = parseInt(target.style.width.replace('px', ''));
                let height = parseInt(target.style.height.replace('px', ''));

                let x1, x2, y1, y2 = 0;

                let rematrixTransform = Rematrix.fromString(target.style.transform);

                // let transformGroups = [...target.style.transform.matchAll(/\((.+?)\)/gm)];
                // if (transformGroups.length === 0) {
                //     return;
                // }
                // let matrix = transformGroups[0][1].split(', ');
                // let transform = transformGroups[1][1].split(', ');

                // x1 = parseInt(matrix[4]) + parseInt(transform[0].replace('px', ''));
                x1 = parseInt(rematrixTransform[12]);
                x2 = x1 + width;

                let top = parseInt(target.style.top.replace('px', ''));
                if (isNaN(top)) {
                    top = 0;
                }

                // y1 = parseInt(matrix[5]) + parseInt(transform[1].replace('px', '')) + top;
                y1 = parseInt(rematrixTransform[13]) + top;
                y2 = y1 + height;

                if (row.max_points == 0) {
                    x2 = x1;
                } else {
                    x2 = Math.max(((row.points / row.max_points) * width) + x1, x1);
                }

                let containerWidth = document.querySelector('.slidemeister-instance').offsetWidth;
                let containerHeight = document.querySelector('.slidemeister-instance').offsetHeight;

                let prizegivingbarCoordinates = {
                    x1: this.normalizeNumber(x1, containerWidth, 10),
                    x2: this.normalizeNumber(x2, containerWidth, 10),
                    y1: this.normalizeNumber(y1, containerHeight, 10),
                    y2: this.normalizeNumber(y2, containerHeight, 10),
                };

                // FIXME: HACK FOR REVISION 2022
                if (prizegivingbarCoordinates.x1 > 0.06) {
                    prizegivingbarCoordinates.x2 = prizegivingbarCoordinates.x2 - prizegivingbarCoordinates.x1 + 0.0510416667;
                    prizegivingbarCoordinates.x1 = 0.0510416667;
                }

                Vue.set(entryElement.properties, 'prizegivingbarCoordinates', prizegivingbarCoordinates);
            });
            // setTimeout(() => {
            // }, 100);
        },
        normalizeNumber(n1, n2, decimals) {
            if (n2 === 0) {
                return 0;
            }

            return Number((n1 / n2).toFixed(decimals));
        }

    }
};
