<?php

namespace Partymeister\Slides\Forms\Backend;

use Kris\LaravelFormBuilder\Form;

/**
 * Class TransitionForm
 *
 * @package Partymeister\Slides\Forms\Backend
 */
class TransitionForm extends Form
{
    /**
     * @return mixed|void
     */
    public function buildForm()
    {
        $this->add('name', 'text', ['label' => trans('motor-backend::backend/global.name'), 'rules' => 'required'])
             ->add('client_type', 'select', [
                 'label'   => trans('partymeister-slides::backend/slide_clients.type'),
                 'choices' => (trans('partymeister-slides::backend/slide_clients.types')),
             ])
             ->add('identifier', 'text', [
                     'label' => trans('partymeister-slides::backend/transitions.identifier'),
                     'rules' => 'required',
                 ])
             ->add('default_duration', 'text', [
                 'label' => trans('partymeister-slides::backend/transitions.default_duration'),
                 'rules' => 'required',
             ])
             ->add('submit', 'submit', [
                 'attr'  => ['class' => 'btn btn-primary'],
                 'label' => trans('partymeister-slides::backend/transitions.save'),
             ]);
    }
}
