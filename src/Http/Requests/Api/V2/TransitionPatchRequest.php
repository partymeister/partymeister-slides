<?php

namespace Partymeister\Slides\Http\Requests\Api\V2;

class TransitionPatchRequest extends TransitionPostRequest
{
    public function rules(): array
    {
        return collect(parent::rules())
            ->mapWithKeys(fn ($rule, $key) => [$key => 'sometimes|'.$rule])
            ->all();
    }
}
