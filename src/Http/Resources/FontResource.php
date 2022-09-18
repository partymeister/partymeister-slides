<?php

namespace Partymeister\Slides\Http\Resources;

use Illuminate\Support\Arr;
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
            'name'   => Arr::get($this, 'name'),
            'path'   => Arr::get($this, 'path'),
            'family' => Arr::get($this, 'family'),
        ];
    }
}
