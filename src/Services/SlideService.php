<?php

namespace Partymeister\Slides\Services;

use Illuminate\Support\Arr;
use Motor\Backend\Models\Category;
use Motor\Backend\Services\BaseService;
use Motor\Core\Filter\Renderers\SelectRenderer;
use Partymeister\Slides\Helpers\ScreenshotHelper;
use Partymeister\Slides\Models\Slide;

/**
 * Class SlideService
 * @package Partymeister\Slides\Services
 */
class SlideService extends BaseService
{

    /**
     * @var string
     */
    protected $model = Slide::class;


    public function filters()
    {
        $this->filter->add(new SelectRenderer('slide_type'))
                     ->setEmptyOption('-- ' . trans('partymeister-slides::backend/slides.slide_type') . ' --')
                     ->setOptions(trans('partymeister-slides::backend/slides.slide_types'));

        $categories = Category::where('scope', 'slides')
                              ->where('_lft', '>', 1)
                              ->orderBy('_lft', 'ASC')
                              ->pluck('name', 'id');
        $this->filter->add(new SelectRenderer('category_id'))
                     ->setEmptyOption('-- ' . trans('motor-backend::backend/categories.categories') . ' --')
                     ->setOptions($categories);
    }


    public function beforeUpdate()
    {
        if ($this->request->get('slide_template_id') == '') {
            $this->data['slide_template_id'] = null;
        }
        $this->beforeCreate();
    }


    public function beforeCreate()
    {
        $this->data['definitions'] = stripslashes($this->request->get('definitions'));
        if ($this->request->get('slide_template_id') == '') {
            $this->data['slide_template_id'] = null;
        }
    }


    public function afterCreate()
    {
        $this->generatePreview();
    }


    protected function generatePreview()
    {
        $browser = new ScreenshotHelper();
        $browser->screenshot(route('backend.slides.show', [ $this->record->id ]).'?preview=true',
            storage_path().'/preview_'.$this->record->id.'.png');

        $this->record->clearMediaCollection('preview');
        $this->record->clearMediaCollection('final');

        $this->record->addMedia(storage_path() . '/preview_' . $this->record->id . '.png')
                     ->toMediaCollection('preview', 'media');
    }


    public function afterUpdate()
    {
        $this->generatePreview();
    }
}
