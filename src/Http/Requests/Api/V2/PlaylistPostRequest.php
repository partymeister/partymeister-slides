<?php

namespace Partymeister\Slides\Http\Requests\Api\V2;

use Illuminate\Foundation\Http\FormRequest;

class PlaylistPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'is_competition' => 'required|boolean',
        ];
    }
}
