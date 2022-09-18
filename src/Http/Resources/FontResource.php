<?php

namespace Partymeister\Slides\Http\Resources;

use Motor\Backend\Http\Resources\BaseResource;

/**
 * @OA\Schema(
 *   schema="FontResource",
 *   @OA\Property(
 *     property="name",
 *     type="string",
 *     example="Exo 2"
 *   ),
 *   @OA\Property(
 *     property="path",
 *     type="string",
 *     example="/fonts/exo2.css"
 *   ),
 *   @OA\Property(
 *     property="family",
 *     type="string",
 *     example="Exo 2"
 *   )
 * )
 */
class FontResource extends BaseResource
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
            'name'   => $this->name,
            'path'   => $this->path,
            'family' => $this->family,
        ];
    }
}
