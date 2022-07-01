<?php

namespace Partymeister\Slides\Http\Requests\Backend;

use Motor\Backend\Http\Requests\Request;

/**
 * Class PlaylistItemRequest
 */
class PlaylistItemRequest extends Request
{
    /**
     * @OA\Schema(
     *   schema="PlaylistItemRequest",
     *   @OA\Property(
     *     property="playlist_id",
     *     type="integer",
     *     example="1"
     *   ),
     *   @OA\Property(
     *     property="type",
     *     type="string",
     *     example="image"
     *   ),
     *   @OA\Property(
     *     property="slide_type",
     *     type="string",
     *     example="announce"
     *   ),
     *   @OA\Property(
     *     property="slide_id",
     *     type="integer",
     *     example="1"
     *   ),
     *   @OA\Property(
     *     property="duration",
     *     type="integer",
     *     example="20"
     *   ),
     *   @OA\Property(
     *     property="transition_id",
     *     type="integer",
     *     example="1"
     *   ),
     *   @OA\Property(
     *     property="transition_slidemeister_id",
     *     type="integer",
     *     example="1"
     *   ),
     *   @OA\Property(
     *     property="transition_duration",
     *     type="integer",
     *     example="2000"
     *   ),
     *   @OA\Property(
     *     property="is_advanced_manually",
     *     type="boolean",
     *     example="false"
     *   ),
     *   @OA\Property(
     *     property="is_muted",
     *     type="boolean",
     *     example="false"
     *   ),
     *   @OA\Property(
     *     property="midi_note",
     *     type="integer",
     *     example="203"
     *   ),
     *   @OA\Property(
     *     property="metadata",
     *     type="json",
     *     example="{}"
     *   ),
     *   @OA\Property(
     *     property="callback_hash",
     *     type="string",
     *     example="102030405060708090"
     *   ),
     *   @OA\Property(
     *     property="callback_delay",
     *     type="integer",
     *     example="20"
     *   ),
     *   @OA\Property(
     *     property="sort_position",
     *     type="integer",
     *     example="1"
     *   ),
     *   required={"playlist_id"},
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
            'playlist_id'                => 'required|integer',
            'type'                       => 'nullable',
            'slide_type'                 => 'nullable',
            'slide_id'                   => 'nullable|integer',
            'duration'                   => 'nullable|integer',
            'transition_id'              => 'nullable|integer',
            'transition_slidemeister_id' => 'nullable|integer',
            'transition_duration'        => 'nullable|integer',
            'is_advanced_manually'       => 'nullable|boolean',
            'is_muted'                   => 'nullable|boolean',
            'midi_note'                  => 'nullable|integer',
            'metadata'                   => 'nullable|json',
            'callback_hash'              => 'nullable',
            'callback_delay'             => 'nullable|integer',
            'sort_position'              => 'nullable|integer',
        ];
    }
}
