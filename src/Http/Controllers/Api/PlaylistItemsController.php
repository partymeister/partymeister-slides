<?php

namespace Partymeister\Slides\Http\Controllers\Api;

use Motor\Backend\Http\Controllers\ApiController;
use Partymeister\Slides\Http\Requests\Backend\PlaylistItemRequest;
use Partymeister\Slides\Http\Resources\PlaylistItemCollection;
use Partymeister\Slides\Http\Resources\PlaylistItemResource;
use Partymeister\Slides\Models\PlaylistItem;
use Partymeister\Slides\Services\PlaylistItemService;

/**
 * Class PlaylistItemsController
 */
class PlaylistItemsController extends ApiController
{
    protected string $model = 'Partymeister\Slides\Models\PlaylistItem';

    protected string $modelResource = 'playlist_item';

    /**
     * @OA\Get (
     *   tags={"PlaylistItemsController"},
     *   path="/api/playlist_items",
     *   summary="Get playlist_item collection",
     *
     *   @OA\Parameter(
     *
     *     @OA\Schema(type="string"),
     *     in="query",
     *     allowReserved=true,
     *     name="api_token",
     *     parameter="api_token",
     *     description="Personal api_token of the user"
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *
     *     @OA\JsonContent(
     *
     *       @OA\Property(
     *         property="data",
     *         type="array",
     *
     *         @OA\Items(ref="#/components/schemas/PlaylistItemResource")
     *       ),
     *
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
     *
     *   @OA\Response(
     *     response="403",
     *     description="Access denied",
     *
     *     @OA\JsonContent(ref="#/components/schemas/AccessDenied"),
     *   )
     * )
     *
     * Display a listing of the resource.
     *
     * @return PlaylistItemCollection
     */
    public function index()
    {
        $paginator = PlaylistItemService::collection()
            ->getPaginator();

        return (new PlaylistItemCollection($paginator))->additional(['message' => 'PlaylistItem collection read']);
    }

    /**
     * @OA\Post (
     *   tags={"PlaylistItemsController"},
     *   path="/api/playlist_items",
     *   summary="Create new playlist_item",
     *
     *   @OA\RequestBody(
     *
     *     @OA\JsonContent(ref="#/components/schemas/PlaylistItemRequest")
     *   ),
     *
     *   @OA\Parameter(
     *
     *     @OA\Schema(type="string"),
     *     in="query",
     *     allowReserved=true,
     *     name="api_token",
     *     parameter="api_token",
     *     description="Personal api_token of the user"
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *
     *     @OA\JsonContent(
     *
     *       @OA\Property(
     *         property="data",
     *         type="object",
     *         ref="#/components/schemas/PlaylistItemResource"
     *       ),
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="PlaylistItem created"
     *       )
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response="403",
     *     description="Access denied",
     *
     *     @OA\JsonContent(ref="#/components/schemas/AccessDenied"),
     *   ),
     *
     *   @OA\Response(
     *     response="404",
     *     description="Not found",
     *
     *     @OA\JsonContent(ref="#/components/schemas/NotFound"),
     *   )
     * )
     *
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(PlaylistItemRequest $request)
    {
        $result = PlaylistItemService::create($request)
            ->getResult();

        return (new PlaylistItemResource($result))->additional(['message' => 'PlaylistItem created'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @OA\Get (
     *   tags={"PlaylistItemsController"},
     *   path="/api/playlist_items/{playlist_item}",
     *   summary="Get single playlist_item",
     *
     *   @OA\Parameter(
     *
     *     @OA\Schema(type="string"),
     *     in="query",
     *     allowReserved=true,
     *     name="api_token",
     *     parameter="api_token",
     *     description="Personal api_token of the user"
     *   ),
     *
     *   @OA\Parameter(
     *
     *     @OA\Schema(type="integer"),
     *     in="path",
     *     name="playlist_item",
     *     parameter="playlist_item",
     *     description="PlaylistItem id"
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *
     *     @OA\JsonContent(
     *
     *       @OA\Property(
     *         property="data",
     *         type="object",
     *         ref="#/components/schemas/PlaylistItemResource"
     *       ),
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="PlaylistItem read"
     *       )
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response="403",
     *     description="Access denied",
     *
     *     @OA\JsonContent(ref="#/components/schemas/AccessDenied"),
     *   ),
     *
     *   @OA\Response(
     *     response="404",
     *     description="Not found",
     *
     *     @OA\JsonContent(ref="#/components/schemas/NotFound"),
     *   )
     * )
     *
     * Display the specified resource.
     *
     * @return PlaylistItemResource
     */
    public function show(PlaylistItem $record)
    {
        $result = PlaylistItemService::show($record)
            ->getResult();

        return (new PlaylistItemResource($result))->additional(['message' => 'PlaylistItem read']);
    }

    /**
     * @OA\Put (
     *   tags={"PlaylistItemsController"},
     *   path="/api/playlist_items/{playlist_item}",
     *   summary="Update an existing playlist_item",
     *
     *   @OA\RequestBody(
     *
     *     @OA\JsonContent(ref="#/components/schemas/PlaylistItemRequest")
     *   ),
     *
     *   @OA\Parameter(
     *
     *     @OA\Schema(type="string"),
     *     in="query",
     *     allowReserved=true,
     *     name="api_token",
     *     parameter="api_token",
     *     description="Personal api_token of the user"
     *   ),
     *
     *   @OA\Parameter(
     *
     *     @OA\Schema(type="integer"),
     *     in="path",
     *     name="playlist_item",
     *     parameter="playlist_item",
     *     description="PlaylistItem id"
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *
     *     @OA\JsonContent(
     *
     *       @OA\Property(
     *         property="data",
     *         type="object",
     *         ref="#/components/schemas/PlaylistItemResource"
     *       ),
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="PlaylistItem updated"
     *       )
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response="403",
     *     description="Access denied",
     *
     *     @OA\JsonContent(ref="#/components/schemas/AccessDenied"),
     *   ),
     *
     *   @OA\Response(
     *     response="404",
     *     description="Not found",
     *
     *     @OA\JsonContent(ref="#/components/schemas/NotFound"),
     *   )
     * )
     *
     * Update the specified resource in storage.
     *
     * @return PlaylistItemResource
     */
    public function update(PlaylistItemRequest $request, PlaylistItem $record)
    {
        $result = PlaylistItemService::update($record, $request)
            ->getResult();

        return (new PlaylistItemResource($result))->additional(['message' => 'PlaylistItem updated']);
    }

    /**
     * @OA\Delete (
     *   tags={"PlaylistItemsController"},
     *   path="/api/playlist_items/{playlist_item}",
     *   summary="Delete a playlist_item",
     *
     *   @OA\Parameter(
     *
     *     @OA\Schema(type="string"),
     *     in="query",
     *     allowReserved=true,
     *     name="api_token",
     *     parameter="api_token",
     *     description="Personal api_token of the user"
     *   ),
     *
     *   @OA\Parameter(
     *
     *     @OA\Schema(type="integer"),
     *     in="path",
     *     name="playlist_item",
     *     parameter="playlist_item",
     *     description="PlaylistItem id"
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *
     *     @OA\JsonContent(
     *
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="PlaylistItem deleted"
     *       )
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response="403",
     *     description="Access denied",
     *
     *     @OA\JsonContent(ref="#/components/schemas/AccessDenied"),
     *   ),
     *
     *   @OA\Response(
     *     response="404",
     *     description="Not found",
     *
     *     @OA\JsonContent(ref="#/components/schemas/NotFound"),
     *   ),
     *
     *   @OA\Response(
     *     response="400",
     *     description="Bad request",
     *
     *     @OA\JsonContent(
     *
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="Problem deleting playlist_item"
     *       )
     *     )
     *   )
     * )
     *
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(PlaylistItem $record)
    {
        $result = PlaylistItemService::delete($record)
            ->getResult();

        if ($result) {
            return response()->json(['message' => 'PlaylistItem deleted']);
        }

        return response()->json(['message' => 'Problem deleting PlaylistItem'], 404);
    }
}
