<?php

namespace Partymeister\Slides\Forms\Backend;

use Kris\LaravelFormBuilder\Form;
use Motor\Admin\Models\Category;

/**
 * Class SlideForm
 */
class SlideForm extends Form
{
    /**
     * @return mixed|void
     */
    public function buildForm()
    {
        $categoryChoices = [];
        $categories = Category::where('scope', 'slides')
                              ->where('_lft', '>', 1)
                              ->orderBy('_lft', 'ASC')
                              ->get();
        foreach ($categories as $category) {
            $indent = '';
            $ancestors = (int) $category->ancestors()
                                        ->count();
            while ($ancestors > 1) {
                $indent .= '&nbsp;&nbsp;&nbsp;';
                $ancestors--;
            }

            $categoryChoices[$category->id] = $indent.$category->name;
        }

        $this->add('name', 'text', ['label' => trans('motor-backend::backend/global.name'), 'rules' => 'required'])
             ->add('category_id', 'select', ['label'   => trans('motor-backend::backend/categories.category'),
                 'choices' => $categoryChoices,
             ])
             ->add('slide_type', 'select', [
                 'label'   => trans('partymeister-slides::backend/slides.slide_type'),
                 'choices' => (trans('partymeister-slides::backend/slides.slide_types')),
             ])
             ->add('slide_template_id', 'hidden')
             ->add('definitions', 'hidden', ['attr' => ['v-pre' => true]])
             ->add('cached_html_preview', 'hidden', ['attr' => ['v-pre' => true]])
             ->add('cached_html_final', 'hidden', ['attr' => ['v-pre' => true]])
             ->add('image_data', 'hidden')
             ->add('submit', 'submit', [
                 'attr'  => ['class' => 'btn btn-primary btn-block slidemeister-save'],
                 'label' => trans('partymeister-slides::backend/slides.save'),
             ]);
    }
}
