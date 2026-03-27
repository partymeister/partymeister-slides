<?php

namespace Partymeister\Slides\Http\Resources\V2;

use Motor\Core\Http\Resources\V2\BaseResource;

class FontResource extends BaseResource
{
    public function toArray($request): array
    {
        return [
            'name' => $this->resource['name'],
            'path' => $this->resource['path'],
            'family' => $this->resource['family'],
        ];
    }
}
