<?php

namespace Partymeister\Slides\Grids;

use Motor\Admin\Grid\Grid;
use Motor\Admin\Grid\Renderers\BladeRenderer;
use Motor\Admin\Grid\Renderers\TranslateRenderer;
use Partymeister\Slides\Grid\Renderers\SlideRenderer;

/**
 * Class SlideGrid
 */
class SlideGrid extends Grid
{
    protected function setup()
    {
        $this->addColumn('preview', trans('motor-media::backend/files.file'))
            ->renderer(SlideRenderer::class, ['file' => 'preview']);
        $this->addColumn('link', trans('motor-media::backend/files.file'))
            ->renderer(BladeRenderer::class, ['template' => 'partymeister-slides::grid.slides.slide']);
        $this->addColumn('name', trans('motor-admin::backend/global.name'), true);
        $this->addColumn('slide_type', trans('partymeister-slides::backend/slides.slide_type'))
            ->renderer(TranslateRenderer::class, ['file' => 'partymeister-slides::backend/slides.slide_types']);
        $this->addColumn('category.name', trans('motor-admin::backend/categories.category'));
        $this->addColumn('controls', trans('partymeister-slides::backend/slide_clients.controls'))
            ->renderer(BladeRenderer::class, ['template' => 'partymeister-slides::grid.slide_clients.playnow_slide_controls']);
        $this->setDefaultSorting('id', 'DESC');

        $this->addAction(trans('motor-admin::backend/global.edit'), 'backend.slidemeister-editor.slide', ['class' => 'btn-warning', 'target' => '_blank']);
        $this->addDuplicateAction(trans('motor-admin::backend/global.duplicate'), 'backend.slides.duplicate')
            ->needsPermissionTo('slides.write');
        $this->addDeleteAction(trans('motor-admin::backend/global.delete'), 'backend.slides.destroy');
    }
}
