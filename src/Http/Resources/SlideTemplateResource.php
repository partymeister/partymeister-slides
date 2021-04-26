<?php

namespace Partymeister\Slides\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *   schema="SlideTemplateResource",
 *   @OA\Property(
 *     property="id",
 *     type="integer",
 *     example="1"
 *   ),
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
 * )
 */
class SlideTemplateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                  => (int) $this->id,
            'name'                => $this->name,
            'template_for'        => $this->template_for,
            'definitions'         => $this->definitions,
            'cached_html_preview' => $this->cached_html_preview,
            'cached_html_final'   => $this->cached_html_final,
        ];
    }
}
