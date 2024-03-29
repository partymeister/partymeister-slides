<?php

namespace Partymeister\Slides\Http\Resources;

use Motor\Backend\Http\Resources\BaseResource;
use Motor\Media\Http\Resources\FileResource;

/**
 * @OA\Schema(
 *   schema="PlaylistItemResource",
 *   @OA\Property(
 *     property="id",
 *     type="integer",
 *     example="1"
 *   ),
 *   @OA\Property(
 *     property="playlist",
 *     type="object",
 *     ref="#/components/schemas/PlaylistResource"
 *   ),
 *   @OA\Property(
 *     property="type",
 *     type="string",
 *     example="image"
 *   ),
 *   @OA\Property(
 *     property="slide_type",
 *     type="string",
 *     example="announce"
 *   ),
 *   @OA\Property(
 *     property="slide",
 *     type="object",
 *     ref="#/components/schemas/SlideResource"
 *   ),
 *   @OA\Property(
 *     property="duration",
 *     type="integer",
 *     example="20"
 *   ),
 *   @OA\Property(
 *     property="transition",
 *     type="object",
 *     ref="#/components/schemas/TransitionResource"
 *   ),
 *   @OA\Property(
 *     property="transition_slidemeister",
 *     type="object",
 *     ref="#/components/schemas/TransitionResource"
 *   ),
 *   @OA\Property(
 *     property="transition_duration",
 *     type="integer",
 *     example="2000"
 *   ),
 *   @OA\Property(
 *     property="is_advanced_manually",
 *     type="boolean",
 *     example="false"
 *   ),
 *   @OA\Property(
 *     property="is_muted",
 *     type="boolean",
 *     example="false"
 *   ),
 *   @OA\Property(
 *     property="midi_note",
 *     type="integer",
 *     example="203"
 *   ),
 *   @OA\Property(
 *     property="metadata",
 *     type="json",
 *     example="{}"
 *   ),
 *   @OA\Property(
 *     property="callback_hash",
 *     type="string",
 *     example="102030405060708090"
 *   ),
 *   @OA\Property(
 *     property="callback_delay",
 *     type="integer",
 *     example="20"
 *   ),
 *   @OA\Property(
 *     property="sort_position",
 *     type="integer",
 *     example="1"
 *   ),
 * )
 */
class PlaylistItemResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                      => (int) $this->id,
            'playlist'                => new PlaylistResource($this->whenLoaded('playlist')),
            'type'                    => $this->type,
            'slide_type'              => $this->slide_type,
            'slide'                   => new SlideResource($this->slide),
            'duration'                => (int) $this->duration,
            'transition'              => new TransitionResource($this->transition),
            'transition_slidemeister' => new TransitionResource($this->transition_slidemeister),
            'transition_duration'     => (int) $this->transition_duration,
            'is_advanced_manually'    => (bool) $this->is_advanced_manually,
            'is_muted'                => (bool) $this->is_muted,
            'midi_note'               => (int) $this->midi_note,
            'metadata'                => json_decode($this->metadata, true),
            'callback_hash'           => $this->callback_hash,
            'callback_delay'          => (int) $this->callback_delay,
            'sort_position'           => (int) $this->sort_position,
            'file_association'        => (! is_null($this->file_association) ? new FileResource($this->file_association->file) : null),
            // FIXME: this is fishy AF
        ];
    }
}
