@section('view_scripts')
    <script type="text/javascript">
        $('.slide-clients-control').on('click', function (e) {
            e.preventDefault();

            data = {
                direction: $(this).data('direction'),
                hard: $(this).data('hard')
            };

            axios.post('{{route('ajax.slide_clients.communication.skip')}}', data).then(function (response) {
                updatePlaylists();
            }).catch(function (error) {
                console.log(error);
            });
        });

        // Get playlists
        let updatePlaylists = function () {

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
                        playlists = parsedResponse.playlists;
                        currentPlaylist = parsedResponse.currentPlaylist;
                        currentItem = parsedResponse.currentItem;
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

                axios.get('/ajax/playlist_items/' + currentItem).then(function (response) {
                    $('.playlist-preview').addClass('d-none');
                    if (response.data.data.file_association && response.data.data.file_association.exists) {
                        $('.playlist-' + currentPlaylist + '-preview').removeClass('d-none');
                        $('.playlist-' + currentPlaylist + '-preview').find('img').prop('src', response.data.data.file_association.file.conversions.preview);
                    }
                    if (response.data.data.slide && response.data.data.slide.file_preview) {
                        $('.playlist-' + currentPlaylist + '-preview').removeClass('d-none');
                        $('.playlist-' + currentPlaylist + '-preview').find('img').prop('src', response.data.data.slide.file_final.conversions.preview);
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

        $('.slide-clients-play').on('click', function (e) {
            e.preventDefault();

            data = {
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
                updatePlaylists();
            })
                .catch(function (error) {
                    console.log(error);
                });
        });

        $('.slide-clients-playnow').on('click', function (e) {
            e.preventDefault();

            if ($(this).data('slide')) {
                data = {
                    slide_id: $(this).data('slide')
                };
                playlistId = 'playnow_slide_' + data.slide_id;
            } else if ($(this).data('file')) {
                data = {
                    file_id: $(this).data('file')
                };
                playlistId = 'playnow_file_' + data.slide_id;
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
