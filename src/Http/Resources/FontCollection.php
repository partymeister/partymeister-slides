<?php

namespace Partymeister\Slides\Http\Resources;

use Illuminate\Http\Request;
use Motor\Admin\Http\Resources\BaseCollection;

class FontCollection extends BaseCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
