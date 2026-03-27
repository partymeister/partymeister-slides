<?php

namespace Partymeister\Slides\Http\Requests\Api\V2;

class SlideTemplatePatchRequest extends SlideTemplatePostRequest
{
    public function rules(): array
    {
        return collect(parent::rules())
            ->mapWithKeys(fn ($rule, $key) => [$key => 'sometimes|'.$rule])
            ->all();
    }
}
