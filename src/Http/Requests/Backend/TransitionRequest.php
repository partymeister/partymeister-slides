<?php

namespace Partymeister\Slides\Http\Requests\Backend;

use Motor\Backend\Http\Requests\Request;

/**
 * Class TransitionRequest
 *
 * @package Partymeister\Slides\Http\Requests\Backend
 */
class TransitionRequest extends Request
{
    /**
     * @OA\Schema(
     *   schema="TransitionRequest",
     *   @OA\Property(
     *     property="name",
     *     type="string",
     *     example="Super transition"
     *   ),
     *   @OA\Property(
     *     property="client_type",
     *     type="string",
     *     example="slidemeister-web"
     *   ),
     *   @OA\Property(
     *     property="identifier",
     *     type="string",
     *     example="swipe-right"
     *   ),
     *   required={"name", "client_type", "identifier"},
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
            'client_type'      => 'required|in:'.implode(',', array_flip(trans('partymeister-slides::backend/slide_clients.types'))),
            'identifier'       => 'required',
            'default_duration' => 'nullable|integer',
        ];
    }
}
