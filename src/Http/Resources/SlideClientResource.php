<?php

namespace Partymeister\Slides\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *   schema="SlideClientResource",
 *   @OA\Property(
 *     property="id",
 *     type="integer",
 *     example="1"
 *   ),
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
 *     property="playlist",
 *     type="object",
 *     ref="#/components/schemas/PlaylistResource"
 *   ),
 *   @OA\Property(
 *     property="playlist_item",
 *     type="object",
 *     ref="#/components/schemas/PlaylistItemResource"
 *   ),
 * )
 */
class SlideClientResource extends JsonResource
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
            'id'            => (int) $this->id,
            'name'          => $this->name,
            'type'          => $this->type,
            'ip_address'    => $this->ip_address,
            'port'          => $this->port,
            'sort_position' => (int) $this->sort_position,
            'configuration' => $this->configuration,
            'playlist'      => new PlaylistResource($this->playlist),
            'slide'         => new SlideResource($this->slide),
        ];
    }
}
