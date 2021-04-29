<?php

namespace Partymeister\Slides\Http\Resources;

use Motor\Backend\Http\Resources\BaseResource;

/**
 * @OA\Schema(
 *   schema="TransitionResource",
 *   @OA\Property(
 *     property="id",
 *     type="integer",
 *     example="1"
 *   ),
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
 * )
 */
class TransitionResource extends BaseResource
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
            'id'          => (int) $this->id,
            'name'        => $this->name,
            'client_type' => $this->client_type,
            'identifier'  => $this->identifier,
        ];
    }
}
