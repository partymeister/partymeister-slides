<?php

namespace Partymeister\Slides\Http\Resources\V2;

use Motor\Core\Http\Resources\V2\BaseResource;
use Partymeister\Slides\Models\SlideTemplate;

/** @mixin SlideTemplate */
class SlideTemplateResource extends BaseResource
{
    public function toArray($request): array
    {
        return [
            'id' => (int) $this->id,
            'name' => $this->name,
            'template_for' => $this->template_for,
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
