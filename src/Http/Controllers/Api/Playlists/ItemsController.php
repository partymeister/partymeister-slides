<?php

namespace Partymeister\Slides\Http\Controllers\Api\Playlists;

use Motor\Backend\Http\Controllers\Controller;
use Partymeister\Slides\Http\Resources\PlaylistItemResource;
use Partymeister\Slides\Models\PlaylistItem;

/**
 * Class ItemsController
 */
class ItemsController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  PlaylistItem  $record
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(PlaylistItem $record)
    {
        $this->playlist = (new PlaylistItemResource($record))->toArrayRecursive();

        return response()->json(['error' => 'Not found'], 404);
    }
}
