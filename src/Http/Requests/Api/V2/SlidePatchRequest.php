<?php

namespace Partymeister\Slides\Http\Requests\Api\V2;

class SlidePatchRequest extends SlidePostRequest
{
    public function rules(): array
    {
        return collect(parent::rules())
            ->mapWithKeys(fn ($rule, $key) => [$key => 'sometimes|'.$rule])
            ->all();
    }
}
