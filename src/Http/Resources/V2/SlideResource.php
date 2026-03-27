<?php

namespace Partymeister\Slides\Http\Resources\V2;

use Motor\Core\Http\Resources\V2\BaseResource;
use Partymeister\Slides\Models\Slide;

/** @mixin Slide */
class SlideResource extends BaseResource
{
    public function toArray($request): array
    {
        return [
            'id' => (int) $this->id,
            'name' => $this->name,
            'slide_type' => $this->slide_type,
            'slide_template' => new SlideTemplateResource($this->whenLoaded('template')),
            'category_id' => $this->category_id ? (int) $this->category_id : null,
            'definitions' => $this->definitions,
            'cached_html_preview' => $this->cached_html_preview,
            'cached_html_final' => $this->cached_html_final,
            'file_preview' => $this->getFirstMediaUrl('preview') ?: null,
            'file_final' => $this->getFirstMediaUrl('final') ?: null,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
