import Echo from "laravel-echo";
let Pusher = require('pusher-js');

Vue.component(
    'partymeister-slides-mediapool',
    require('./components/Mediapool.vue').default
);

Vue.component(
    'partymeister-slides-playlist',
    require('./components/Playlist.vue').default
);
Vue.component(
    'partymeister-slides-screenshot',
    require('./components/Screenshot.vue').default
);

// Initialize global event hub
Vue.prototype.$eventHub = new Vue();

const server = new Echo({
    broadcaster: 'pusher',
    key: window.websocket.key,
    wsHost: window.websocket.host,
    wsPort: window.websocket.port,
    wsPath: window.websocket.path,
    enabledTransports: ['wss', 'ws'],
    disableStats: true,
});

console.log("LISTENING TO CHANNEL");
server.channel('partymeister.slidemeister-web.screenshot-update')
    .listen('.Partymeister\\Slides\\Events\\ScreenshotUpdated', (e) => {
        console.log("Screenshot updated event received", e.slide);
        Vue.prototype.$eventHub.$emit('screenshot-updated', {slide: e.slide});
    })


//             this.server.connector.pusher.connection.bind('unavailable', () => {
//                 this.$eventHub.$emit('socket-unavailable');
//             });
//             this.server.connector.pusher.connection.bind('connected', () => {
//                 this.toast('Socket connection established');
//                 this.$eventHub.$emit('socket-connected');
//             });
//         },
//         removeListeners() {
//             console.log("LEAVING CHANNEL");
//             this.server.leaveChannel('partymeister.slidemeister-web.screenshot-update');
//         },
//         addListeners() {
//             console.log("LISTENING TO CHANNEL");
//             this.listening = true;
//             this.server.channel('partymeister.slidemeister-web.screenshot-update')
//                 .listen('.Partymeister\\Slides\\Events\\ScreenshotUpdated', (e) => {
//                 })
//         }
//     }
// }
