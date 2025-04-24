<?php

return [
    'groups' => [
        'partymeister-slides' => [
            'name' => 'Partymeister slides',
        ],
    ],
    'components' => [
        'playlist-viewers' => [
            'name' => 'PlaylistViewer',
            'description' => 'Show PlaylistViewer component',
            'view' => 'partymeister-slides::frontend.components.playlist-viewers',
            'route' => 'component.playlist-viewers',
            'component_class' => 'Partymeister\Slides\Components\ComponentPlaylistViewers',
            'compatibility' => [

            ],
            'permissions' => [

            ],
            'group' => 'partymeister-slides',
        ],
    ],
];
