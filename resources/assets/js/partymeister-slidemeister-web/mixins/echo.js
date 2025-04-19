import Echo from "laravel-echo";

let Pusher = require('pusher-js');
import Vue from 'vue';
import {getFromStorage, saveToStorage} from "./storage";

let siegmeisterInProgress = false;

export default {
    data: function () {
        return {
            server: null,
            serverConfiguration: {},
            listening: false,
        };
    },
    async created() {
        this.$eventHub.$on('server-configuration-update', async () => {
            console.log("[START] Server configuration update event received");
            let serverConfiguration = await getFromStorage('serverConfiguration');
            if (serverConfiguration === null || serverConfiguration === undefined) {
                console.log("[START] No server configuration found");
                return;
            }

            Vue.set(this, 'serverConfiguration', serverConfiguration);

            if (this.listening) {
                this.removeListeners();
            }
            this.createServer();
            this.addListeners();
        });

        console.log('[START] Starting socket connection');

        let serverConfiguration = await getFromStorage('serverConfiguration');
        if (serverConfiguration === null || serverConfiguration === undefined) {
            console.log("[START] No server configuration found");
            return;
        }
        Vue.set(this, 'serverConfiguration', serverConfiguration);
        if (this.listening) {
            this.removeListeners();
        }
        this.createServer();
        this.addListeners();
    },
    beforeDestroy() {
        this.$eventHub.$off();
    },
    methods: {
        createServer() {
            console.log("[SOCKET] Creating server connection");
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
            console.log("[SOCKET] Leaving channel", 'partymeister.slidemeister-web.' + this.serverConfiguration.client);
            this.server.leaveChannel('partymeister.slidemeister-web.' + this.serverConfiguration.client);
        },
        addListeners() {
            console.log("[SOCKET] Listening to channel", 'partymeister.slidemeister-web.' + this.serverConfiguration.client);
            this.listening = true;
            this.server.channel('partymeister.slidemeister-web.' + this.serverConfiguration.client)
                .listen('.Partymeister\\Slides\\Events\\PlayNowRequest', (e) => {
                    console.log('[PlayNowRequest] incoming');
                    if (this.playlist.id !== undefined) {
                        // console.log('Playlist is running - saving position and playlist');
                        this.playlistSaved = this.playlist;
                        this.currentItemSaved = this.currentItem;
                    }

                    let item = {};

                    if (e.item.playnow_type === 'slide') {
                        item = {
                            duration: 20,
                            is_advanced_manually: true,
                            transition_slidemeister: {
                                identifier: "255"
                            },
                            transition_duration: 2000,
                            callback_hash: '',
                            callback_delay: 20,
                            slide: e.item,
                            type: e.item.type,
                        }
                    } else {
                        item = {
                            duration: 20,
                            is_advanced_manually: true,
                            transition_slidemeister: {
                                identifier: "255"
                            },
                            transition_duration: 2000,
                            callback_hash: '',
                            callback_delay: 20,
                            file_association: e.item,
                            type: e.item.type,
                        }
                    }


                    if (this.playNow === true) {
                        this.playNowItems.push(item);
                    } else {
                        this.playNowItems = [item]
                    }
                    // this.playNow = true;
                    this.nextPlayNowItem = this.playNowItems.length - 1;

                    this.seekToPlayNow();
                })
                .listen('.Partymeister\\Slides\\Events\\PlaylistNextRequest', (e) => {
                    console.log('[PlaylistNextRequest] incoming');
                    if (this.playlist.id == undefined) {
                        // console.log('No playlist is running, aborting');
                    } else {
                        this.playNow = false;
                        this.seekToNextItem(e.hard);
                    }
                    this.updateStatus();
                })
                .listen('.Partymeister\\Slides\\Events\\PlaylistPreviousRequest', (e) => {
                    console.log('[PlaylistPreviousRequest] incoming');
                    if (this.playlist.id == undefined) {
                        // console.log('No playlist is running, aborting');
                    } else {
                        this.playNow = false;
                        this.seekToPreviousItem(e.hard);
                    }
                    this.updateStatus();
                })
                .listen('.Partymeister\\Slides\\Events\\SiegmeisterRequest', (e) => {
                    console.log("[SiegmeisterRequest] request received");
                    if (siegmeisterInProgress === false) {
                        this.renderPrizegivingBars();
                    }
                    siegmeisterInProgress = true;
                    setTimeout(() => {
                        siegmeisterInProgress = false;
                    }, 2000);
                })
                .listen('.Partymeister\\Slides\\Events\\PlaylistRequest', async (e) => {
                    console.log('[PlaylistRequest] incoming request for playlist', e.playlist.name);

                    let found = false;
                    for (const [index, p] of this.cachedPlaylists.entries()) {
                        if (p.id === e.playlist.id) {
                            console.log('[PlaylistRequest] Playlist exists, checking if it needs to be updated');
                            // Update callback status
                            this.cachedPlaylists[index].callbacks = e.playlist.callbacks;
                            if (p.updated_at !== e.playlist.updated_at.date) {
                                console.log('[PlaylistRequest] outdated, checking if it is currently playing');
                                this.cachedPlaylists[index] = e.playlist;
                                if (this.playlist.id === p.id) {
                                    console.log('[PlaylistRequest] is currently playing. Updating');
                                    if (this.currentItem > (e.playlist.items.length - 1)) {
                                        console.log('[PlaylistRequest] Updated playlist has fewer items, resetting index of currently playing item');
                                        this.currentItem = e.items.length - 1;
                                    }

                                    this.playlist = e.playlist;
                                    this.items = e.playlist.items;

                                    await saveToStorage('cachedPlaylists', this.cachedPlaylists);
                                    await saveToStorage('currentItem', this.currentItem);
                                    await saveToStorage('playlist', this.playlist);
                                }
                            }
                            found = true;
                        }
                    }
                    if (!found) {
                        console.log('[PlaylistRequest] Playlist does not exist yet. Caching it', e.playlist.name);
                        this.cachedPlaylists.push(e.playlist);
                        await saveToStorage('cachedPlaylists', this.cachedPlaylists);
                    }
                    this.updateStatus();
                })
                .listen('.Partymeister\\Slides\\Events\\PlaylistSeekRequest', async (e) => {
                    console.log('[PlaylistSeekRequest] PlaylistSeekRequest incoming', e.playlist_id, e.index);

                    let found = false;
                    for (const [index, p] of this.cachedPlaylists.entries()) {
                        if (p.id === e.playlist_id) {
                            console.log('[PlaylistSeekRequest] Playlist exists, seeking to item', p.name, e.index);
                            if (this.playlist.id === e.playlist_id) {
                                console.log('[PlaylistSeekRequest] Playlist is running, seeking to item', e.index);
                                if (e.index === false) {
                                    e.index = parseInt(await getFromStorage('currentItem'));
                                    if (!e.index) {
                                        e.index = 0;
                                    }
                                }
                                this.seekToIndex(parseInt(e.index));
                            } else {
                                console.log('[PlaylistSeekRequest] Playlist is not running yet. Setting it and seeking to item', p.name, e.index);
                                this.playlistSaved = this.playlist;
                                this.currentItemSaved = this.currentItem;

                                this.playlist = p;
                                this.items = p.items;
                                this.playNow = false;

                                await saveToStorage('playlist', this.playlist);
                                setTimeout(async () => {
                                    if (e.index === false) {
                                        e.index = parseInt(await getFromStorage('currentItem'));
                                        if (!e.index) {
                                            console.log('[PlaylistSeekRequest] No index found, setting to 0');
                                            e.index = 0;
                                        }
                                    }
                                    this.seekToIndex(parseInt(e.index));

                                    // reset after transition
                                    setTimeout(() => {
                                        this.playlistSaved = null ;
                                        this.currentItemSaved = null ;
                                    }, 500);
                                }, 200);
                            }
                            found = true;
                        }
                    }
                    if (!found) {
                        console.log('[PlaylistSeekRequest] Playlist not found, cannot seek to index ' + e.index);
                    }
                    this.updateStatus();
                });
        }
    }
}
