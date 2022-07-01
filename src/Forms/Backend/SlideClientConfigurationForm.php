<?php

namespace Partymeister\Slides\Forms\Backend;

use Kris\LaravelFormBuilder\Form;

/**
 * Class SlideClientForm
 */
class SlideClientConfigurationForm extends Form
{
    /**
     * @return mixed|void
     */
    public function buildForm()
    {
        $this->add('midi_note_jingle_1', 'text', ['label' => trans('partymeister-slides::backend/slide_clients.jingle_midi_note').' (F1)'])
             ->add('midi_note_jingle_2', 'text', ['label' => trans('partymeister-slides::backend/slide_clients.jingle_midi_note').' (F2)'])
             ->add('midi_note_jingle_3', 'text', ['label' => trans('partymeister-slides::backend/slide_clients.jingle_midi_note').' (F3)'])
             ->add('midi_note_jingle_4', 'text', ['label' => trans('partymeister-slides::backend/slide_clients.jingle_midi_note').' (F4)'])
             ->add('prizegiving_bar_color', 'colorpicker', ['label' => trans('partymeister-slides::backend/slide_clients.prizegiving_bar_color')])
             ->add('prizegiving_bar_blink_color', 'colorpicker', ['label' => trans('partymeister-slides::backend/slide_clients.prizegiving_bar_blink_color')]);

        foreach (trans('partymeister-slides::backend/slides.slide_types') as $type => $name) {
            $this->add('fragment_'.$type, 'textarea', ['label' => trans('partymeister-slides::backend/slide_clients.fragment', ['type' => $name])]);
//            $this->add(
//                'copy_from_'.$type,
//                'select',
//                [ 'label' => trans('partymeister-slides::backend/slide_clients.copy_from') ]
//            );
        }
    }
}
