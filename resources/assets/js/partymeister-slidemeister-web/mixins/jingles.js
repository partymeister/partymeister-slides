import WebMidi from "webmidi";

export default {
    data: function () {
        return {
            jingles: [],
            jingle: null,
        };
    },
    created() {
        this.$eventHub.$on('jingles-loaded', (jingles) => {
            console.log("Jingles loaded", jingles);
            this.jingles = jingles;
        });
    },
    methods: {
        playJingle(index) {
            if (this.jingles[index] !== undefined) {
                this.jingle = this.jingles[index];
                let player = document.querySelector('#jingle-player > audio');
                console.log(player);
                setTimeout(() => {
                    player.play();
                    if (WebMidi.outputs.length > 0 && parseInt(this.configuration['midi_note_' + index]) > 0) {
                        WebMidi.outputs[0].playNote(parseInt(this.configuration['midi_note_' + index]), 1, {velocity: 1, duration: 1000});
                        console.log("Played midi note for jingle " + this.configuration['midi_note_' + index] + ' to device ' + WebMidi.outputs[0].name + ' ('+  WebMidi.outputs[0].id + ')');
                    }
                }, 10);
            }
        },
        playMidi(index) {
            setTimeout(() => {
                if (WebMidi.outputs.length > 0 && parseInt(this.configuration['midi_note_' + index]) > 0) {
                    WebMidi.outputs[0].playNote(parseInt(this.configuration['midi_note_' + index]), 1, {velocity: 1, duration: 1000});
                    console.log("Played midi note for index " + this.configuration['midi_note_' + index] + ' to device ' + WebMidi.outputs[0].name + ' ('+  WebMidi.outputs[0].id + ')');
                }
            }, 10);
        },
    }
}
