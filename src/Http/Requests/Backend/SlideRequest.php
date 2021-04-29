<?php

namespace Partymeister\Slides\Http\Requests\Backend;

use Motor\Backend\Http\Requests\Request;

/**
 * Class SlideRequest
 *
 * @package Partymeister\Slides\Http\Requests\Backend
 */
class SlideRequest extends Request
{
    /**
     * @OA\Schema(
     *   schema="SlideRequest",
     *   @OA\Property(
     *     property="name",
     *     type="string",
     *     example="My first slide"
     *   ),
     *   @OA\Property(
     *     property="slide_template_id",
     *     type="integer",
     *     example="1"
     *   ),
     *   @OA\Property(
     *     property="slide_type",
     *     type="string",
     *     example="announce"
     *   ),
     *   @OA\Property(
     *     property="category_id",
     *     type="integer",
     *     example="1"
     *   ),
     *   @OA\Property(
     *     property="definitions",
     *     type="json",
     *     example="{}"
     *   ),
     *   @OA\Property(
     *     property="cached_html_preview",
     *     type="string"
     *   ),
     *   @OA\Property(
     *     property="cached_html_final",
     *     type="string"
     *   ),
     *   required={"name", "slide_type", "category_id"},
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
            'name'                => 'required',
            'slide_template_id'   => 'nullable|integer',
            'slide_type'          => 'required|in:'.implode(',', array_flip(trans('partymeister-slides::backend/slides.slide_types'))),
            'category_id'         => 'required|integer',
            'definitions'         => 'nullable|string',
            'cached_html_preview' => 'nullable',
            'cached_html_final'   => 'nullable',
        ];
    }
}
