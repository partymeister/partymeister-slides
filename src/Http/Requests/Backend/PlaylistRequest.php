<?php

namespace Partymeister\Slides\Http\Requests\Backend;

use Motor\Backend\Http\Requests\Request;

/**
 * Class PlaylistRequest
 *
 * @package Partymeister\Slides\Http\Requests\Backend
 */
class PlaylistRequest extends Request
{
    /**
     * @OA\Schema(
     *   schema="PlaylistRequest",
     *   @OA\Property(
     *     property="name",
     *     type="string",
     *     example="Main rotation"
     *   ),
     *   @OA\Property(
     *     property="type",
     *     type="string",
     *     example="video"
     *   ),
     *   @OA\Property(
     *     property="is_competition",
     *     type="boolean",
     *     example="false"
     *   ),
     *   required={"name", "type"},
     * )
     */

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'           => 'required',
            'type'           => 'required|in:'.implode(',', array_flip(trans('partymeister-slides::backend/playlists.types'))),
            'is_competition' => 'nullable|boolean',
        ];
    }
}
