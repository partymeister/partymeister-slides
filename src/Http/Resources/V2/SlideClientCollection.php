<?php

namespace Partymeister\Slides\Http\Resources\V2;

use Motor\Core\Http\Resources\V2\BaseCollection;

class SlideClientCollection extends BaseCollection
{
    public $collects = SlideClientResource::class;

    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
