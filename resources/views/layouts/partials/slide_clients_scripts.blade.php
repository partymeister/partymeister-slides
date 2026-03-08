@section('view_scripts')
    <script type="module">
        $('.slide-clients-control').on('click', function (e) {
            e.preventDefault();

            let data = {
                direction: $(this).data('direction'),
                hard: $(this).data('hard')
            };

            axios.post('{{route('ajax.slide_clients.communication.skip')}}', data).then(function (response) {
                updatePlaylists();
            }).catch(function (error) {
                console.log(error);
            });
        });

        // Get playlists — exposed on window so other module scripts can call it
        window.updatePlaylists = function () {

            axios.get('{{route('ajax.slide_clients.communication.playlists')}}').then(function (playlistsResponse) {

                let parsedResponse = null;
                let playlists = [];
                let currentItem = null;
                let currentPlaylist = null;
                let validPlaylist = false;

                parsedResponse = $.parseXML(playlistsResponse.data.result);
                if (parsedResponse == null) {
                    console.log('UpdatePlaylists: Response is not an xml document');
                } else {
                    validPlaylist = true;
                    playlists = $(parsedResponse).find('data playlist');
                    $(parsedResponse).find('data playlist').each(function (index, element) {
                        playlists.push({
                            id: $(element).find('name').text(),
                            updated_at: parseInt($(element).find('timestamp').text())
                        });
                    });

                    let currentItemCombined = $(parsedResponse).find('data item_current').text();
                    let split = currentItemCombined.split('_');

                    currentPlaylist = split[0];
                    currentItem = split[1];
                }

                if (!validPlaylist) {
                    parsedResponse = playlistsResponse.data.result;
                    if (typeof parsedResponse !== 'object') {
                        console.log('UpdatePlaylists: Response it not a json object. Aborting');
                        return;
                    } else {
                        playlists = parsedResponse.cached_playlists || parsedResponse.playlists || [];
                        currentPlaylist = parsedResponse.current_playlist_id || parsedResponse.currentPlaylist;
                        currentItem = parsedResponse.current_item_id || parsedResponse.currentItem;
                    }
                }

                $('.playlist-cached').addClass('d-none');
                $('.playlist-playing').addClass('d-none');
                $('.playlist-outdated').addClass('d-none');

                for (let p of playlists) {
                    $('.playlist-' + p.id + '-cached').removeClass('d-none');

                    let remotePlaylistTimestamp = p.updated_at;
                    let playlistTimestamp = parseInt($('.playlist-' + p.id + '-outdated').data('timestamp'));

                    if (remotePlaylistTimestamp !== null && remotePlaylistTimestamp < playlistTimestamp) {
                        $('.playlist-' + p.id + '-outdated').removeClass('d-none');
                    }
                }

                $('.playlist-' + currentPlaylist + '-playing').removeClass('d-none');

                if (!currentItem) {
                    return;
                }

                axios.get('/ajax/playlist_items/' + currentItem).then(function (response) {
                    $('.playlist-preview').addClass('d-none');
                    let previewUrl = null;
                    if (response.data.data.file_association && response.data.data.file_association.exists) {
                        previewUrl = response.data.data.file_association.file.conversions.preview;
                    }
                    if (response.data.data.slide && response.data.data.slide.file_preview) {
                        previewUrl = response.data.data.slide.file_preview.conversions.preview;
                    }
                    if (previewUrl) {
                        let $container = $('.playlist-' + currentPlaylist + '-preview');
                        $container.removeClass('d-none');
                        $container.find('img').prop('src', previewUrl);
                        $container.find('a.playlist-preview-link').attr('href', previewUrl).attr('data-fancybox', 'playlist-preview');
                    }
                }).catch(function (error) {
                    console.log('UpdatePlaylists: Playlist item not found');
                    console.log(error);
                });


            }).catch(function (error) {
                console.log('UpdatePlaylists: Response is empty');
                console.error(error);
            });
        };

        let seek = function (data) {
            return axios.post('{{route('ajax.slide_clients.communication.seek')}}', data).then(function (response) {
                updatePlaylists();
            }).catch(function (error) {
                console.log(error);
            });
        };

        let seekContinue = function (data) {
            console.log("SCHMORP");
            return axios.post('{{route('ajax.slide_clients.communication.seek_continue')}}', data).then(function (response) {
                updatePlaylists();
            }).catch(function (error) {
                console.log(error);
            });
        };

        $('.slide-clients-play').on('click', function (e) {
            e.preventDefault();

            let data = {
                playlist_id: $(this).data('playlist'),
                callbacks: $(this).data('callbacks')
            };

            let action = $(this).data('action');

            if (data.callbacks === 1) {
                if (!confirm('{{ trans('partymeister-slides::backend/slide_clients.callback_question') }}')) {
                    return false;
                }
            }

            axios.post('{{route('ajax.slide_clients.communication.playlist')}}', data).then(function (response) {

                if (action === 'seek') {
                    let seekData = {
                        playlist_id: data.playlist_id,
                        hard: false,
                    };

                    seek(seekData).then(() => {
                        updatePlaylists();
                    });
                    return;
                }

                if (action === 'seek_continue') {
                    let seekData = {
                        playlist_id: data.playlist_id,
                        hard: false,
                    };

                    seekContinue(seekData).then(() => {
                        updatePlaylists();
                    });
                    return;
                }
                updatePlaylists();
            })
                .catch(function (error) {
                    console.log(error);
                });
        });

        $('.slide-clients-playnow').on('click', function (e) {
            e.preventDefault();

            let data = {};
            let playlistId = '';
            if ($(this).data('slide')) {
                data = {
                    slide_id: $(this).data('slide')
                };
                playlistId = 'playnow_slide_' + data.slide_id;
            } else if ($(this).data('file')) {
                data = {
                    file_id: $(this).data('file')
                };
                playlistId = 'playnow_file_' + data.file_id;
            }

            axios.post('{{route('ajax.slide_clients.communication.playnow')}}', data).then(function (response) {
                // be happy, nothing broke!
            })
                .catch(function (error) {
                    console.log(error);
                });
        });
    </script>

@append
