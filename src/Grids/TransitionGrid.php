<?php

namespace Partymeister\Slides\Grids;

use Motor\Backend\Grid\Grid;
use Motor\Backend\Grid\Renderers\TranslateRenderer;

/**
 * Class TransitionGrid
 */
class TransitionGrid extends Grid
{
    protected function setup()
    {
        $this->addColumn('name', trans('motor-backend::backend/global.name'), true);
        $this->addColumn('client_type', trans('partymeister-slides::backend/slide_clients.type'))
            ->renderer(TranslateRenderer::class, ['file' => 'partymeister-slides::backend/slide_clients.types']);
        $this->addColumn('identifier', trans('partymeister-slides::backend/transitions.identifier'), true);
        $this->addColumn('default_duration', trans('partymeister-slides::backend/transitions.default_duration'), true);
        $this->setDefaultSorting('name', 'ASC');
        $this->addEditAction(trans('motor-backend::backend/global.edit'), 'backend.transitions.edit');
        $this->addDeleteAction(trans('motor-backend::backend/global.delete'), 'backend.transitions.destroy');
    }
}
