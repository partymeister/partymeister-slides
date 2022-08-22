import Echo from "laravel-echo";

let Pusher = require('pusher-js');
import Vue from 'vue';

let siegmeisterInProgress = false;

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
                serverConfiguration = JSON.parse(serverConfiguration);

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
            this.server.leaveChannel('partymeister.slidemeister-web.' + this.serverConfiguration.client);
        },
        addListeners() {
            console.log("LISTENING TO CHANNEL");
            this.listening = true;
            this.server.channel('partymeister.slidemeister-web.' + this.serverConfiguration.client)
                .listen('.Partymeister\\Slides\\Events\\PlayNowRequest', (e) => {
                    console.log('PlayNowRequest incoming');
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
                    console.log('PlaylistNextRequest incoming');
                    if (this.playlist.id == undefined) {
                        // console.log('No playlist is running, aborting');
                    } else {
                        this.playNow = false;
                        this.seekToNextItem(e.hard);
                    }
                    this.updateStatus();
                })
                .listen('.Partymeister\\Slides\\Events\\PlaylistPreviousRequest', (e) => {
                    console.log('PlaylistPreviousRequest incoming');
                    if (this.playlist.id == undefined) {
                        // console.log('No playlist is running, aborting');
                    } else {
                        this.playNow = false;
                        this.seekToPreviousItem(e.hard);
                    }
                    this.updateStatus();
                })
                .listen('.Partymeister\\Slides\\Events\\SiegmeisterRequest', (e) => {
                    console.log("Siegmeister request received");
                    if (siegmeisterInProgress === false) {
                        this.renderPrizegivingBars();
                    }
                    siegmeisterInProgress = true;
                    setTimeout(() => {
                        siegmeisterInProgress = false;
                    }, 2000);
                })
                .listen('.Partymeister\\Slides\\Events\\PlaylistRequest', (e) => {
                    // console.log('PlaylistRequest incoming');

                    let found = false;
                    for (const [index, p] of this.cachedPlaylists.entries()) {
                        if (p.id === e.playlist.id) {
                            // console.log('Playlist exists, checking if it needs to be updated');
                            // Update callback status
                            this.cachedPlaylists[index].callbacks = e.playlist.callbacks;
                            if (p.updated_at !== e.playlist.updated_at.date) {
                                // console.log('Playlist outdated, checking if it is currently playing');
                                this.cachedPlaylists[index] = e.playlist;
                                if (this.playlist.id === p.id) {
                                    // console.log('Playlist is currently playing. Updating');
                                    if (this.currentItem > (e.playlist.items.length - 1)) {
                                        // console.log('Updated playlist has fewer items, resetting index of currently playing item');
                                        this.currentItem = e.items.length - 1;
                                    }

                                    this.playlist = e.playlist;
                                    this.items = e.playlist.items;

                                    localStorage.setItem('cachedPlaylists', JSON.stringify(this.cachedPlaylists));
                                    localStorage.setItem('currentItem', this.currentItem);
                                    localStorage.setItem('playlist', JSON.stringify(this.playlist));
                                }
                            }
                            found = true;
                        }
                    }
                    if (!found) {
                        console.log('Playlist does not exist yet. Caching it');
                        this.cachedPlaylists.push(e.playlist);
                        localStorage.setItem('cachedPlaylists', JSON.stringify(this.cachedPlaylists));
                    }
                    this.updateStatus();
                })
                .listen('.Partymeister\\Slides\\Events\\PlaylistSeekRequest', (e) => {
                    console.log('PlaylistSeekRequest incoming');

                    let found = false;
                    for (const [index, p] of this.cachedPlaylists.entries()) {
                        if (p.id === e.playlist_id) {
                            console.log('Playlist exists, seeking to item ' + e.index);
                            if (this.playlist.id === e.playlist_id) {
                                console.log('Playlist is running, seeking to item ' + e.index);
                                this.seekToIndex(parseInt(e.index));
                            } else {
                                console.log('Playlist is not running yet. Setting it and seeking to item ' + e.index);
                                this.playlist = p;
                                this.items = p.items;
                                this.playNow = false;
                                localStorage.setItem('playlist', JSON.stringify(this.playlist));
                                setTimeout(() => {
                                    this.seekToIndex(parseInt(e.index));
                                }, 200);
                            }

                            found = true;
                        }
                    }
                    if (!found) {
                        console.log('Playlist not found, cannot seek to index ' + e.index);
                    }
                    this.updateStatus();
                });
        }
    }
}
