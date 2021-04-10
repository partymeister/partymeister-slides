<?php

return [
    'items' => [
        130 => [
            'slug'        => 'partymeister-slides',
            'name'        => 'partymeister-slides::backend/global.slides',
            'icon'        => 'fa fa-image',
            'route'       => null,
            'roles'       => [ 'SuperAdmin' ],
            'permissions' => [ 'slides.read' ],
            'items'       => [
                100 => [ // <-- !!! replace 277 with your own sort position !!!
                    'slug'        => 'playlists',
                    'name'        => 'partymeister-slides::backend/playlists.playlists',
                    'icon'        => 'fa fa-plus',
                    'route'       => 'backend.playlists.index',
                    'roles'       => [ 'SuperAdmin' ],
                    'permissions' => [ 'playlists.read' ],
                ],
                200 => [ // <-- !!! replace 488 with your own sort position !!!
                    'slug'        => 'slides',
                    'name'        => 'partymeister-slides::backend/slides.slides',
                    'icon'        => 'fa fa-angle-right',
                    'route'       => 'backend.slides.index',
                    'roles'       => [ 'SuperAdmin' ],
                    'permissions' => [ 'slides.read' ],
                ],
                300 => [ // <-- !!! replace 842 with your own sort position !!!
                    'slug'        => 'slide_templates',
                    'name'        => 'partymeister-slides::backend/slide_templates.slide_templates',
                    'icon'        => 'fa fa-angle-right',
                    'route'       => 'backend.slide_templates.index',
                    'roles'       => [ 'SuperAdmin' ],
                    'permissions' => [ 'slide_templates.read' ],
                ],
                310 => [ // <-- !!! replace 157 with your own sort position !!!
                    'slug' => 'slide_clients',
                    'name'  => 'partymeister-slides::backend/slide_clients.slide_clients',
                    'icon'  => 'fa fa-plus',
                    'route' => 'backend.slide_clients.index',
                    'roles'       => [ 'SuperAdmin' ],
                    'permissions' => [ 'slide_clients.read' ],
                ],
                400 => [ // <-- !!! replace 308 with your own sort position !!!
                    'slug'        => 'transitions',
                    'name'        => 'partymeister-slides::backend/transitions.transitions',
                    'icon'        => 'fa fa-plus',
                    'route'       => 'backend.transitions.index',
                    'roles'       => [ 'SuperAdmin' ],
                    'permissions' => [ 'transitions.read' ],
                ],
            ]
        ],
    ]
];
