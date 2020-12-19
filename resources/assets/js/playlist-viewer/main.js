window.Vue = require('vue');

Vue.prototype.$eventHub = new Vue();

require('./main');

import VueAgile from 'vue-agile'

Vue.use(VueAgile);

Vue.component(
    'playlist-viewer',
    require('./components/PlaylistViewer.vue').default
);

// Initialize base vue app
const app = new Vue({
    el: '#app'
});

