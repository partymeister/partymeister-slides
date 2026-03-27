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
use Partymeister\Slides\Services\PlaylistService;

class PlaylistsController extends ApiController
{
    protected string $model = Playlist::class;

    protected string $modelResource = 'playlist';

    public function index(PlaylistGetRequest $request): PlaylistCollection
    {
        $paginator = PlaylistService::collection()->getPaginator();

        return (new PlaylistCollection($paginator))
            ->additional(['meta' => ['message' => 'Playlists retrieved']]);
    }

    public function store(PlaylistPostRequest $request): JsonResponse
    {
        $result = PlaylistService::create($request)->getResult();

        return (new PlaylistResource($result))
            ->additional(['meta' => ['message' => 'Playlist created']])
            ->response()->setStatusCode(201);
    }

    public function show(Playlist $playlist): PlaylistResource
    {
        $result = PlaylistService::show($playlist)->getResult();

        return (new PlaylistResource($result))
            ->additional(['meta' => ['message' => 'Playlist retrieved']]);
    }

    public function update(PlaylistPatchRequest $request, Playlist $playlist): PlaylistResource
    {
        $result = PlaylistService::update($playlist, $request)->getResult();

        return (new PlaylistResource($result))
            ->additional(['meta' => ['message' => 'Playlist updated']]);
    }

    public function destroy(Playlist $playlist): Response
    {
        $result = PlaylistService::delete($playlist)->getResult();
        if ($result) {
            return $this->noContentResponse();
        }
        abort(404, 'Problem deleting playlist');
    }
}
