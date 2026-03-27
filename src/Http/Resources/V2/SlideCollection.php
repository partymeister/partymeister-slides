<?php

namespace Partymeister\Slides\Http\Resources\V2;

use Motor\Core\Http\Resources\V2\BaseCollection;

class SlideCollection extends BaseCollection
{
    public $collects = SlideResource::class;

    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
