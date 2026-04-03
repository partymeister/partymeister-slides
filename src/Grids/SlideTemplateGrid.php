<?php

namespace Partymeister\Slides\Grids;

use Motor\Backend\Grid\Grid;
use Motor\Backend\Grid\Renderers\BladeRenderer;
use Motor\Backend\Grid\Renderers\FileRenderer;
use Motor\Backend\Grid\Renderers\TranslateRenderer;
use Partymeister\Slides\Grid\Renderers\SlideRenderer;

/**
 * Class SlideTemplateGrid
 */
class SlideTemplateGrid extends Grid
{
    protected function setup()
    {
        $this->addColumn('preview', trans('motor-media::backend/files.file'))
            ->renderer(SlideRenderer::class, ['file' => 'preview']);
        $this->addColumn('link', trans('motor-media::backend/files.file'))
             ->renderer(BladeRenderer::class, ['template' => 'partymeister-slides::grid.slide_templates.slide_template']);
        $this->addColumn('name', trans('motor-backend::backend/global.name'), true);
        $this->addColumn('template_for', trans('partymeister-slides::backend/slide_templates.template_for'))
             ->renderer(TranslateRenderer::class, ['file' => 'partymeister-slides::backend/slide_templates.template_for_types']);
        $this->setDefaultSorting('name', 'ASC');
        $this->addAction(trans('partymeister-slides::backend/slides.create_from_template'), 'backend.slidemeister-editor.create-from-template', ['class' => 'btn-primary', 'target' => '_blank']);
        $this->addAction(trans('motor-backend::backend/global.edit'), 'backend.slidemeister-editor.template', ['class' => 'btn-warning', 'target' => '_blank']);
        $this->addDuplicateAction(trans('motor-backend::backend/global.duplicate'), 'backend.slide_templates.duplicate')
             ->needsPermissionTo('slide_templates.write');
        $this->addDeleteAction(trans('motor-backend::backend/global.delete'), 'backend.slide_templates.destroy');
    }
}
