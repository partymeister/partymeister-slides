<?php

namespace Partymeister\Slides\Grids;

use Motor\Backend\Grid\Grid;
use Motor\Backend\Grid\Renderers\BladeRenderer;
use Motor\Backend\Grid\Renderers\CollectionRenderer;
use Motor\Backend\Grid\Renderers\FileRenderer;

/**
 * Class FileGrid
 */
class FileGrid extends Grid
{
    protected function setup()
    {
        $this->addColumn('preview', trans('motor-media::backend/files.file'))
            ->renderer(FileRenderer::class, ['file' => 'file']);
        $this->addColumn('preview_name', trans('motor-media::backend/files.file_info'))
            ->renderer(FileRenderer::class, ['file' => 'file', 'name_only' => true]);
        $this->addColumn('description', trans('motor-media::backend/files.description'));
        $this->addColumn('author', trans('motor-media::backend/files.author'));
        $this->addColumn('categories', trans('motor-backend::backend/categories.categories'))
            ->renderer(CollectionRenderer::class, ['column' => 'name']);
        $this->addColumn('controls', trans('partymeister-slides::backend/slide_clients.controls'))
            ->renderer(BladeRenderer::class, ['template' => 'partymeister-slides::grid.slide_clients.playnow_file_controls']);
        $this->addEditAction(trans('motor-backend::backend/global.edit'), 'backend.files.edit');
        $this->addDeleteAction(trans('motor-backend::backend/global.delete'), 'backend.files.destroy');
    }
}
