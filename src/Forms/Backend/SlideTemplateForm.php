<?php

namespace Partymeister\Slides\Forms\Backend;

use Kris\LaravelFormBuilder\Form;

/**
 * Class SlideTemplateForm
 *
 * @package Partymeister\Slides\Forms\Backend
 */
class SlideTemplateForm extends Form
{
    /**
     * @return mixed|void
     */
    public function buildForm()
    {
        $this->add('name', 'text', ['label' => trans('motor-backend::backend/global.name'), 'rules' => 'required'])
             ->add('template_for', 'select', [
                 'label'   => trans('partymeister-slides::backend/slide_templates.template_for'),
                 'choices' => (trans('partymeister-slides::backend/slide_templates.template_for_types')),
             ])
             ->add('definitions', 'hidden')
             ->add('cached_html_preview', 'hidden')
             ->add('cached_html_final', 'hidden')
             ->add('image_data', 'hidden')
             ->add('submit', 'submit', [
                 'attr'  => ['class' => 'btn btn-primary btn-block slidemeister-save'],
                 'label' => trans('partymeister-slides::backend/slide_templates.save'),
             ]);
    }
}
