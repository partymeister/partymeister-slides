<?php

namespace Partymeister\Slides\Http\Controllers\Api\V2\Playlists;

use Illuminate\Routing\Controller;
use Partymeister\Slides\Http\Resources\V2\PlaylistItemCollection;
use Partymeister\Slides\Models\Playlist;

class ItemsController extends Controller
{
    public function index(Playlist $playlist): PlaylistItemCollection
    {
        $items = $playlist->items()
            ->with(['slide.media', 'transition', 'transition_slidemeister', 'file_association.file'])
            ->orderBy('sort_position')
            ->paginate();

        return (new PlaylistItemCollection($items))
            ->additional(['meta' => ['message' => 'Playlist items retrieved']]);
    }
}
