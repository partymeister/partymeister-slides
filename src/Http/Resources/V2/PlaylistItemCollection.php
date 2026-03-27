<?php

namespace Partymeister\Slides\Http\Resources\V2;

use Motor\Core\Http\Resources\V2\BaseCollection;

class PlaylistItemCollection extends BaseCollection
{
    public $collects = PlaylistItemResource::class;

    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
