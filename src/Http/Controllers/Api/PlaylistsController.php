<?php

namespace Partymeister\Slides\Http\Controllers\Api;

use Motor\Backend\Http\Controllers\ApiController;
use Partymeister\Slides\Http\Requests\Backend\PlaylistRequest;
use Partymeister\Slides\Http\Resources\PlaylistCollection;
use Partymeister\Slides\Http\Resources\PlaylistResource;
use Partymeister\Slides\Models\Playlist;
use Partymeister\Slides\Services\PlaylistService;

/**
 * Class PlaylistsController
 */
class PlaylistsController extends ApiController
{
    protected string $model = 'Partymeister\Slides\Models\Playlist';

    protected string $modelResource = 'playlist';

    /**
     * @OA\Get (
     *   tags={"PlaylistsController"},
     *   path="/api/playlists",
     *   summary="Get playlist collection",
     *   @OA\Parameter(
     *     @OA\Schema(type="string"),
     *     in="query",
     *     allowReserved=true,
     *     name="api_token",
     *     parameter="api_token",
     *     description="Personal api_token of the user"
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *       @OA\Property(
     *         property="data",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/PlaylistResource")
     *       ),
     *       @OA\Property(
     *         property="meta",
     *         ref="#/components/schemas/PaginationMeta"
     *       ),
     *       @OA\Property(
     *         property="links",
     *         ref="#/components/schemas/PaginationLinks"
     *       ),
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="Collection read"
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response="403",
     *     description="Access denied",
     *     @OA\JsonContent(ref="#/components/schemas/AccessDenied"),
     *   )
     * )
     *
     * Display a listing of the resource.
     *
     * @return PlaylistCollection
     */
    public function index()
    {
        $paginator = PlaylistService::collection()
                                    ->getPaginator();

        return (new PlaylistCollection($paginator))->additional(['message' => 'Playlist collection read']);
    }

    /**
     * @OA\Post (
     *   tags={"PlaylistsController"},
     *   path="/api/playlists",
     *   summary="Create new playlist",
     *   @OA\RequestBody(
     *     @OA\JsonContent(ref="#/components/schemas/PlaylistRequest")
     *   ),
     *   @OA\Parameter(
     *     @OA\Schema(type="string"),
     *     in="query",
     *     allowReserved=true,
     *     name="api_token",
     *     parameter="api_token",
     *     description="Personal api_token of the user"
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *       @OA\Property(
     *         property="data",
     *         type="object",
     *         ref="#/components/schemas/PlaylistResource"
     *       ),
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="Playlist created"
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response="403",
     *     description="Access denied",
     *     @OA\JsonContent(ref="#/components/schemas/AccessDenied"),
     *   ),
     *   @OA\Response(
     *     response="404",
     *     description="Not found",
     *     @OA\JsonContent(ref="#/components/schemas/NotFound"),
     *   )
     * )
     *
     * Store a newly created resource in storage.
     *
     * @param  PlaylistRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(PlaylistRequest $request)
    {
        $result = PlaylistService::create($request)
                                 ->getResult();

        return (new PlaylistResource($result))->additional(['message' => 'Playlist created'])
                                              ->response()
                                              ->setStatusCode(201);
    }

    /**
     * @OA\Get (
     *   tags={"PlaylistsController"},
     *   path="/api/playlists/{playlist}",
     *   summary="Get single playlist",
     *   @OA\Parameter(
     *     @OA\Schema(type="string"),
     *     in="query",
     *     allowReserved=true,
     *     name="api_token",
     *     parameter="api_token",
     *     description="Personal api_token of the user"
     *   ),
     *   @OA\Parameter(
     *     @OA\Schema(type="integer"),
     *     in="path",
     *     name="playlist",
     *     parameter="playlist",
     *     description="Playlist id"
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *       @OA\Property(
     *         property="data",
     *         type="object",
     *         ref="#/components/schemas/PlaylistResource"
     *       ),
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="Playlist read"
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response="403",
     *     description="Access denied",
     *     @OA\JsonContent(ref="#/components/schemas/AccessDenied"),
     *   ),
     *   @OA\Response(
     *     response="404",
     *     description="Not found",
     *     @OA\JsonContent(ref="#/components/schemas/NotFound"),
     *   )
     * )
     *
     * Display the specified resource.
     *
     * @param  Playlist  $record
     * @return PlaylistResource
     */
    public function show(Playlist $record)
    {
        $result = PlaylistService::show($record)
                                 ->getResult();

        return (new PlaylistResource($result))->additional(['message' => 'Playlist read']);
    }

    /**
     * @OA\Put (
     *   tags={"PlaylistsController"},
     *   path="/api/playlists/{playlist}",
     *   summary="Update an existing playlist",
     *   @OA\RequestBody(
     *     @OA\JsonContent(ref="#/components/schemas/PlaylistRequest")
     *   ),
     *   @OA\Parameter(
     *     @OA\Schema(type="string"),
     *     in="query",
     *     allowReserved=true,
     *     name="api_token",
     *     parameter="api_token",
     *     description="Personal api_token of the user"
     *   ),
     *   @OA\Parameter(
     *     @OA\Schema(type="integer"),
     *     in="path",
     *     name="playlist",
     *     parameter="playlist",
     *     description="Playlist id"
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *       @OA\Property(
     *         property="data",
     *         type="object",
     *         ref="#/components/schemas/PlaylistResource"
     *       ),
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="Playlist updated"
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response="403",
     *     description="Access denied",
     *     @OA\JsonContent(ref="#/components/schemas/AccessDenied"),
     *   ),
     *   @OA\Response(
     *     response="404",
     *     description="Not found",
     *     @OA\JsonContent(ref="#/components/schemas/NotFound"),
     *   )
     * )
     *
     * Update the specified resource in storage.
     *
     * @param  PlaylistRequest  $request
     * @param  Playlist  $record
     * @return PlaylistResource
     */
    public function update(PlaylistRequest $request, Playlist $record)
    {
        $result = PlaylistService::update($record, $request)
                                 ->getResult();

        return (new PlaylistResource($result))->additional(['message' => 'Playlist updated']);
    }

    /**
     * @OA\Delete (
     *   tags={"PlaylistsController"},
     *   path="/api/playlists/{playlist}",
     *   summary="Delete a playlist",
     *   @OA\Parameter(
     *     @OA\Schema(type="string"),
     *     in="query",
     *     allowReserved=true,
     *     name="api_token",
     *     parameter="api_token",
     *     description="Personal api_token of the user"
     *   ),
     *   @OA\Parameter(
     *     @OA\Schema(type="integer"),
     *     in="path",
     *     name="playlist",
     *     parameter="playlist",
     *     description="Playlist id"
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="Playlist deleted"
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response="403",
     *     description="Access denied",
     *     @OA\JsonContent(ref="#/components/schemas/AccessDenied"),
     *   ),
     *   @OA\Response(
     *     response="404",
     *     description="Not found",
     *     @OA\JsonContent(ref="#/components/schemas/NotFound"),
     *   ),
     *   @OA\Response(
     *     response="400",
     *     description="Bad request",
     *     @OA\JsonContent(
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="Problem deleting playlist"
     *       )
     *     )
     *   )
     * )
     *
     * Remove the specified resource from storage.
     *
     * @param  Playlist  $record
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Playlist $record)
    {
        $result = PlaylistService::delete($record)
                                 ->getResult();

        if ($result) {
            return response()->json(['message' => 'Playlist deleted']);
        }

        return response()->json(['message' => 'Problem deleting Playlist'], 404);
    }
}
