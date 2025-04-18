import Echo from "laravel-echo";

let Pusher = require('pusher-js');
import Vue from 'vue';

export default {
    data: function () {
        return {
            server: null,
            serverConfiguration: {},
            listening: false,
        };
    },
    created() {
        this.$eventHub.$on('server-configuration-update', () => {
            let serverConfiguration = localStorage.getItem('serverConfiguration');
            if (serverConfiguration !== null && serverConfiguration !== undefined) {

                Vue.set(this, 'serverConfiguration', serverConfiguration);

                if (this.listening) {
                    this.removeListeners();
                }
                this.createServer();
                this.addListeners();
            }
        });

        let serverConfiguration = localStorage.getItem('serverConfiguration');
        if (this.standalone && serverConfiguration === null) {
            this.$router.push({name: 'configuration'});
        } else if (serverConfiguration !== null) {
            serverConfiguration = JSON.parse(serverConfiguration);

            Vue.set(this, 'serverConfiguration', serverConfiguration);
            if (this.listening) {
                this.removeListeners();
            }
            this.createServer();
            this.addListeners();
        }
    },
    beforeDestroy() {
        this.$eventHub.$off();
    },
    methods: {
        createServer() {
            delete this.server;

            this.server = new Echo({
                broadcaster: 'pusher',
                key: this.serverConfiguration.key,
                wsHost: this.serverConfiguration.host,
                wsPort: this.serverConfiguration.port,
                wsPath: this.serverConfiguration.path,
                enabledTransports: ['wss', 'ws'],
                disableStats: true,
            });

            this.server.connector.pusher.connection.bind('unavailable', () => {
                this.$eventHub.$emit('socket-unavailable');
            });
            this.server.connector.pusher.connection.bind('connected', () => {
                this.toast('Socket connection established');
                this.$eventHub.$emit('socket-connected');
            });
        },
        removeListeners() {
            console.log("LEAVING CHANNEL");
            this.server.leaveChannel('partymeister.slidemeister-web.screenshot-update');
        },
        addListeners() {
            console.log("LISTENING TO CHANNEL");
            this.listening = true;
            this.server.channel('partymeister.slidemeister-web.screenshot-update')
                .listen('.Partymeister\\Slides\\Events\\ScreenshotUpdated', (e) => {
                })
        }
    }
}
