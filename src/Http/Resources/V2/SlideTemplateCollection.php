<?php

namespace Partymeister\Slides\Http\Resources\V2;

use Motor\Core\Http\Resources\V2\BaseCollection;

class SlideTemplateCollection extends BaseCollection
{
    public $collects = SlideTemplateResource::class;

    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
