<template>
    <div class="server-error alert alert-danger d-none">
        {{error}}
    </div>
</template>

<script>
import { saveToStorage} from "../mixins/storage";

    const axios = require('axios');
    import toast from "../mixins/toast";

    export default {
        data() {
            return {
                error: false
            };
        },
        mixins: [
            toast,
        ],
        mounted() {
            this.$eventHub.$on('socket-unavailable', () => {
                this.error = 'Socket connection not available. Please check your configuration';
                document.querySelector('.server-error').classList.remove('d-none');
            });
            this.$eventHub.$on('socket-connected', () => {
                this.error = false;
                document.querySelector('.server-error').classList.add('d-none');
            });

            this.getConfigFromServer();
        },
        methods: {
            async getConfigFromServer() {
                let url = BASE_URL + '/api/slide_clients/' + window.location.pathname.substring(window.location.pathname.lastIndexOf('/') + 1);
                // Get data from partymeister server (jingles etc.)
                axios.get(url+'?api_token='+TOKEN).then(async result => {
                    console.log("Query done - saving slideClientConfiguration to storage");
                    await saveToStorage('slideClientConfiguration', JSON.stringify(result.data.data));
                    //localStorage.setItem('slideClientConfiguration', JSON.stringify(result.data.data));
                    this.$eventHub.$emit('slide-client-loaded');
                    this.error = false;
                    let serverConfiguration = result.data.data.websocket;
                    serverConfiguration.client = result.data.data.id;
                    await saveToStorage('serverConfiguration', JSON.stringify(serverConfiguration));
                    // localStorage.setItem('serverConfiguration', JSON.stringify(serverConfiguration));
                    document.querySelector('.server-error').classList.add('d-none');
                    this.toast('Slide client configuration loaded');
                    this.$eventHub.$emit('server-configuration-update');
                    this.$eventHub.$emit('jingles-loaded', result.data.data.jingles);
                }).catch(e => {
                    this.error = 'Problems getting slide client configuration from server. Please check your configuration. (' + e.message + ')';
                    document.querySelector('.server-error').classList.remove('d-none');
                });
            }
        }
    }
</script>

<style lang="scss">
    .server-error {
        margin-bottom: 0;
        text-align: center;
        z-index: 40000;
    }
</style>
