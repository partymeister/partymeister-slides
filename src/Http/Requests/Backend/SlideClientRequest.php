<?php

namespace Partymeister\Slides\Http\Requests\Backend;

use Motor\Backend\Http\Requests\Request;

/**
 * Class SlideClientRequest
 */
class SlideClientRequest extends Request
{
    /**
     * @OA\Schema(
     *   schema="SlideClientRequest",
     *   @OA\Property(
     *     property="name",
     *     type="string",
     *     example="Main screen"
     *   ),
     *   @OA\Property(
     *     property="type",
     *     type="string",
     *     example="slidemeister-web"
     *   ),
     *   @OA\Property(
     *     property="ip_address",
     *     type="string",
     *     example="10.10.10.10"
     *   ),
     *   @OA\Property(
     *     property="port",
     *     type="string",
     *     example="80"
     *   ),
     *   @OA\Property(
     *     property="sort_position",
     *     type="integer",
     *     example="1"
     *   ),
     *   @OA\Property(
     *     property="configuration",
     *     type="json",
     *     example="{}"
     *   ),
     *   @OA\Property(
     *     property="playlist_id",
     *     type="integer",
     *     example="1"
     *   ),
     *   @OA\Property(
     *     property="playlist_item_id",
     *     type="integer",
     *     example="1"
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
            'name'             => 'required',
            'type'             => 'required|in:'.implode(',', array_flip(trans('partymeister-slides::backend/slide_clients.types'))),
            'ip_address'       => 'nullable',
            'port'             => 'nullable',
            'sort_position'    => 'nullable|integer',
            'configuration'    => 'nullable|array',
            'playlist_id'      => 'nullable|integer',
            'playlist_item_id' => 'nullable|integer',
        ];
    }
}
