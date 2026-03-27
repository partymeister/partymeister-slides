<?php

namespace Partymeister\Slides\Http\Resources\V2;

use Motor\Admin\Http\Resources\MediaResource;
use Motor\Core\Http\Resources\V2\BaseResource;
use Partymeister\Slides\Models\SlideClient;

/** @mixin SlideClient */
class SlideClientResource extends BaseResource
{
    public function toArray($request): array
    {
        $jingles = [];
        foreach ($this->file_associations as $file) {
            if ($file->file && $file->file->getFirstMedia('file')) {
                $f = new MediaResource($file->file->getFirstMedia('file'));
                $jingles[$file->identifier] = $f->getUrl();
            }
        }

        $configuration = $this->configuration ?? [];
        $configuration['server'] = config('app.url');
        $configuration['client'] = $this->id;

        return [
            'id' => (int) $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'ip_address' => $this->ip_address,
            'port' => $this->port,
            'sort_position' => (int) $this->sort_position,
            'playlist' => new PlaylistResource($this->whenLoaded('playlist')),
            'playlist_item' => new PlaylistItemResource($this->whenLoaded('playlistItem')),
            'configuration' => $configuration,
            'jingles' => $jingles,
            'websocket' => [
                'key' => config('broadcasting.connections.reverb.key'),
                'host' => config('broadcasting.connections.reverb.options.host', 'localhost'),
                'port' => (int) config('broadcasting.connections.reverb.options.port', 80),
                'path' => config('broadcasting.connections.reverb.options.path', '/socket'),
            ],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
