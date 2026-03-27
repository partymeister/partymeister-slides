<?php

namespace Partymeister\Slides\Http\Requests\Api\V2;

use Illuminate\Foundation\Http\FormRequest;

class SlideGetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
