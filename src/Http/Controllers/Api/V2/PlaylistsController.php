<?php

namespace Partymeister\Slides\Http\Controllers\Api\V2;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Motor\Core\Http\Controllers\Api\V2\ApiController;
use Partymeister\Slides\Http\Requests\Api\V2\PlaylistGetRequest;
use Partymeister\Slides\Http\Requests\Api\V2\PlaylistPatchRequest;
use Partymeister\Slides\Http\Requests\Api\V2\PlaylistPostRequest;
use Partymeister\Slides\Http\Resources\V2\PlaylistCollection;
use Partymeister\Slides\Http\Resources\V2\PlaylistResource;
use Partymeister\Slides\Models\Playlist;

/**
 * Uses Playlist model directly instead of PlaylistService for CRUD operations.
 *
 * Playlist items are managed separately: read via GET /playlists/{id}/items,
 * written via PlaylistService::savePlaylistItems() (called by backend forms
 * and competition playlist generators, not by this controller).
 *
 * @tags Playlists
 */
class PlaylistsController extends ApiController
{
    protected string $model = Playlist::class;

    protected string $modelResource = 'playlist';

    /**
     * @response Illuminate\Http\Resources\Json\AnonymousResourceCollection<Illuminate\Pagination\LengthAwarePaginator<PlaylistResource>>
     */
    public function index(PlaylistGetRequest $request): PlaylistCollection
    {
        $paginator = Playlist::paginate();

        return (new PlaylistCollection($paginator))
            ->additional(['meta' => ['message' => 'Playlists retrieved']]);
    }

    public function store(PlaylistPostRequest $request): JsonResponse
    {
        $result = Playlist::create($request->validated());

        return (new PlaylistResource($result))
            ->additional(['meta' => ['message' => 'Playlist created']])
            ->response()->setStatusCode(201);
    }

    public function show(Playlist $playlist): PlaylistResource
    {
        $playlist->load(['items.slide.media', 'items.transition', 'items.transition_slidemeister', 'items.file_association.file']);

        return (new PlaylistResource($playlist))
            ->additional(['meta' => ['message' => 'Playlist retrieved']]);
    }

    public function update(PlaylistPatchRequest $request, Playlist $playlist): PlaylistResource
    {
        $playlist->update($request->validated());

        return (new PlaylistResource($playlist))
            ->additional(['meta' => ['message' => 'Playlist updated']]);
    }

    public function destroy(Playlist $playlist): Response
    {
        if ($playlist->delete()) {
            return $this->noContentResponse();
        }
        abort(404, 'Problem deleting playlist');
    }
}
