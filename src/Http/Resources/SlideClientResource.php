<?php

namespace Partymeister\Slides\Http\Resources;

use Motor\Backend\Http\Resources\BaseResource;
use Motor\Backend\Http\Resources\MediaResource;

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
 *   @OA\Property(
 *     property="jingles",
 *     type="array",
 *     @OA\Items(
 *       ref="#/components/schemas/MediaResource"
 *     ),
 *   ),
 *   @OA\Property(
 *     property="websocket",
 *     type="array",
 *     @OA\Items(
 *       @OA\Property(
 *         property="key",
 *         type="string",
 *         example="123456"
 *       ),
 *       @OA\Property(
 *         property="host",
 *         type="string",
 *         example="https://slides.partymeister.org"
 *       ),
 *       @OA\Property(
 *         property="port",
 *         type="string",
 *         example="80"
 *       ),
 *       @OA\Property(
 *         property="path",
 *         type="string",
 *         example="https://slides.partymeister.org"
 *       ),
 *     ),
 *   ),
 * )
 */
class SlideClientResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $jingles = [];
        foreach ($this->file_associations as $file) {
            $jingles[] = new MediaResource($file->file->getFirstMedia('file'));
        }

        $configuration = $this->configuration;

        $configuration['server'] = config('app.url');
        $configuration['client'] = $this->id;

        return [
            'id'            => (int) $this->id,
            'name'          => $this->name,
            'type'          => $this->type,
            'ip_address'    => $this->ip_address,
            'port'          => $this->port,
            'sort_position' => (int) $this->sort_position,
            'playlist'      => new PlaylistResource($this->playlist),
            'slide'         => new SlideResource($this->slide),
            'configuration' => $configuration,
            'jingles'       => $jingles,
            'websocket'     => [
                'key'  => config('broadcasting.connections.pusher.key'),
                'host' => config('partymeister-slides-slidemeister.host'),
                'port' => config('partymeister-slides-slidemeister.port'),
                'path' => config('partymeister-slides-slidemeister.path'),
            ],
        ];
    }
}
