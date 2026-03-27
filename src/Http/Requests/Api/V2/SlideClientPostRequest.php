<?php

namespace Partymeister\Slides\Http\Requests\Api\V2;

use Illuminate\Foundation\Http\FormRequest;

class SlideClientPostRequest extends FormRequest
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
            'ip_address' => 'required|string|max:255',
            'port' => 'required|string|max:10',
            'sort_position' => 'required|integer',
            'configuration' => 'nullable|array',
            'playlist_id' => 'nullable|integer|exists:playlists,id',
            'playlist_item_id' => 'nullable|integer|exists:playlist_items,id',
        ];
    }
}
