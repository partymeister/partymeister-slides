<?php

namespace Partymeister\Slides\Http\Resources\V2;

use Motor\Core\Http\Resources\V2\BaseResource;
use Partymeister\Slides\Models\PlaylistItem;

/** @mixin PlaylistItem */
class PlaylistItemResource extends BaseResource
{
    public function toArray($request): array
    {
        return [
            'id' => (int) $this->id,
            'type' => $this->type,
            'slide_type' => $this->slide_type,
            'slide' => new SlideResource($this->whenLoaded('slide')),
            'duration' => (int) $this->duration,
            'transition' => new TransitionResource($this->whenLoaded('transition')),
            'transition_slidemeister' => new TransitionResource($this->whenLoaded('transition_slidemeister')),
            'transition_duration' => (int) $this->transition_duration,
            'is_advanced_manually' => (bool) $this->is_advanced_manually,
            'is_muted' => (bool) $this->is_muted,
            'midi_note' => (int) $this->midi_note,
            'metadata' => $this->metadata,
            'callback_hash' => $this->callback_hash,
            'callback_delay' => (int) $this->callback_delay,
            'sort_position' => (int) $this->sort_position,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
