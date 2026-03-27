<?php

namespace Partymeister\Slides\Http\Requests\Api\V2;

use Illuminate\Foundation\Http\FormRequest;

class SlidePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slide_type' => 'required|string|max:255',
            'slide_template_id' => 'nullable|integer|exists:slide_templates,id',
            'category_id' => 'nullable|integer',
            'definitions' => 'required|string',
        ];
    }
}
