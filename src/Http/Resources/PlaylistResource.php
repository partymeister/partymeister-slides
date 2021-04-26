<?php

namespace Partymeister\Slides\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *   schema="PlaylistResource",
 *   @OA\Property(
 *     property="id",
 *     type="integer",
 *     example="1"
 *   ),
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
 * )
 */
class PlaylistResource extends JsonResource
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
            'id'             => (int) $this->id,
            'name'           => $this->name,
            'type'           => $this->type,
            'is_competition' => (boolean) $this->is_competition,
        ];
    }
}
