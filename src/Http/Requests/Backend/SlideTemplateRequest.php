<?php

namespace Partymeister\Slides\Http\Requests\Backend;

use Motor\Backend\Http\Requests\Request;

/**
 * Class SlideTemplateRequest
 *
 * @package Partymeister\Slides\Http\Requests\Backend
 */
class SlideTemplateRequest extends Request
{
    /**
     * @OA\Schema(
     *   schema="SlideTemplateRequest",
     *   @OA\Property(
     *     property="name",
     *     type="string",
     *     example="Slide template"
     *   ),
     *   @OA\Property(
     *     property="template_for",
     *     type="string",
     *     example="basic"
     *   ),
     *   @OA\Property(
     *     property="definitions",
     *     type="json",
     *     example="{}"
     *   ),
     *   @OA\Property(
     *     property="cached_html_preview",
     *     type="text",
     *   ),
     *   @OA\Property(
     *     property="cached_html_final",
     *     type="text",
     *   ),
     *   required={"name", "template_for"},
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
            'template_for'        => 'required|in:'.trans('partymeister-slides::backend/slide_templates.template_for_types'),
            'definitions'         => 'nullable|json',
            'cached_html_preview' => 'nullable',
            'cached_html_final'   => 'nullable',
        ];
    }
}
