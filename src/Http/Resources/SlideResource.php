<?php

namespace Partymeister\Slides\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Motor\Backend\Http\Resources\CategoryResource;

/**
 * @OA\Schema(
 *   schema="SlideResource",
 *   @OA\Property(
 *     property="id",
 *     type="integer",
 *     example="1"
 *   ),
 *   @OA\Property(
 *     property="name",
 *     type="string",
 *     example="My first slide"
 *   ),
 *   @OA\Property(
 *     property="slide_template",
 *     type="object",
 *     ref="#/components/schemas/SlideTemplateResource"
 *   ),
 *   @OA\Property(
 *     property="slide_type",
 *     type="string",
 *     example="announce"
 *   ),
 *   @OA\Property(
 *     property="category",
 *     type="object",
 *     ref="#/components/schemas/CategoryResource"
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
class SlideResource extends JsonResource
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
            'slide_template'      => new SlideTemplateResource($this->slide_template),
            'slide_type'          => $this->slide_type,
            'category'            => new CategoryResource($this->category),
            'definitions'         => $this->definitions,
            'cached_html_preview' => $this->cached_html_preview,
            'cached_html_final'   => $this->cached_html_final,
        ];
    }
}
