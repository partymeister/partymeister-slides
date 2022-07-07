<?php

namespace Partymeister\Slides\Grids;

use Motor\Admin\Grid\Grid;
use Motor\Admin\Grid\Renderers\BladeRenderer;
use Motor\Admin\Grid\Renderers\TranslateRenderer;

/**
 * Class PlaylistGrid
 */
class PlaylistGrid extends Grid
{
    protected function setup()
    {
        $this->addColumn('name', trans('motor-backend::backend/global.name'), true);
        $this->addColumn('type', trans('partymeister-slides::backend/playlists.type'), true)
             ->renderer(TranslateRenderer::class, ['file' => 'partymeister-slides::backend/playlists.types']);
        $this->addColumn('item_count', trans('partymeister-slides::backend/playlists.items'));
        $this->addColumn('controls', trans('partymeister-slides::backend/slide_clients.controls'))
             ->renderer(BladeRenderer::class, ['template' => 'partymeister-slides::grid.slide_clients.playlist_controls']);
        $this->setDefaultSorting('name', 'ASC');

        $this->addEditAction(trans('motor-backend::backend/global.edit'), 'backend.playlists.edit');
        $this->addDeleteAction(trans('motor-backend::backend/global.delete'), 'backend.playlists.destroy');
    }
}
