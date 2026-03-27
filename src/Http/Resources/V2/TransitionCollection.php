<?php

namespace Partymeister\Slides\Http\Resources\V2;

use Motor\Core\Http\Resources\V2\BaseCollection;

class TransitionCollection extends BaseCollection
{
    public $collects = TransitionResource::class;

    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
