<?php

namespace Partymeister\Slides\Http\Requests\Api\V2;

use Illuminate\Foundation\Http\FormRequest;

class TransitionPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'client_type' => 'required|string|max:255',
            'identifier' => 'required|string|max:255',
            'default_duration' => 'required|integer',
        ];
    }
}
