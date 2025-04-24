<?php

namespace Partymeister\Slides\Services;

use Motor\Backend\Services\BaseService;
use Motor\Core\Filter\Renderers\SelectRenderer;
use Partymeister\Slides\Models\Transition;

/**
 * Class TransitionService
 */
class TransitionService extends BaseService
{
    /**
     * @var string
     */
    protected $model = Transition::class;

    public function filters()
    {
        $this->filter->add(new SelectRenderer('client_type'))
            ->setOptionPrefix(trans('partymeister-slides::backend/slide_clients.type'))
            ->setEmptyOption('-- '.trans('partymeister-slides::backend/slide_clients.type').' --')
            ->setOptions(trans('partymeister-slides::backend/slide_clients.types'));
    }
}
