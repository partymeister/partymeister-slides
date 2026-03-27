<?php

namespace Partymeister\Slides\Http\Resources\V2;

use Motor\Core\Http\Resources\V2\BaseCollection;

class PlaylistCollection extends BaseCollection
{
    public $collects = PlaylistResource::class;

    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
