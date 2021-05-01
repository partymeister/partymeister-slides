<?php

namespace Partymeister\Slides\Forms\Backend\Component;

use Kris\LaravelFormBuilder\Form;
use Partymeister\Slides\Models\Playlist;

class ComponentPlaylistViewerForm extends Form
{
    public function buildForm()
    {
        $this->add('playlist_id', 'select', [
            'label'       => trans('partymeister-slides::backend/playlists.playlist'),
            'empty_value' => trans('motor-backend::backend/global.please_choose'),
            'choices'     => Playlist::pluck('name', 'id')
                                     ->toArray(),
        ]);
    }
}
