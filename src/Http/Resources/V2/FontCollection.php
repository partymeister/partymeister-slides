<?php

namespace Partymeister\Slides\Http\Resources\V2;

use Motor\Core\Http\Resources\V2\BaseCollection;

class FontCollection extends BaseCollection
{
    public $collects = FontResource::class;

    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
