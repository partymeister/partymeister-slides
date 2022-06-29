<?php

namespace Partymeister\Slides\Http\Controllers\Api;

use Motor\Backend\Http\Controllers\ApiController;
use Partymeister\Slides\Http\Requests\Backend\SlideClientRequest;
use Partymeister\Slides\Http\Resources\SlideClientCollection;
use Partymeister\Slides\Http\Resources\SlideClientResource;
use Partymeister\Slides\Models\SlideClient;
use Partymeister\Slides\Services\SlideClientService;

/**
 * Class SlideClientsController
 */
class SlideClientsController extends ApiController
{
    protected string $model = 'Partymeister\Slides\Models\SlideClient';

    protected string $modelResource = 'slide_client';

    /**
     * @OA\Get (
     *   tags={"SlideClientsController"},
     *   path="/api/slide_clients",
     *   summary="Get slide_client collection",
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
     *         @OA\Items(ref="#/components/schemas/SlideClientResource")
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
     * @return SlideClientCollection
     */
    public function index()
    {
        $paginator = SlideClientService::collection()
                                       ->getPaginator();

        return (new SlideClientCollection($paginator))->additional(['message' => 'SlideClient collection read']);
    }

    /**
     * @OA\Post (
     *   tags={"SlideClientsController"},
     *   path="/api/slide_clients",
     *   summary="Create new slide_client",
     *   @OA\RequestBody(
     *     @OA\JsonContent(ref="#/components/schemas/SlideClientRequest")
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
     *         ref="#/components/schemas/SlideClientResource"
     *       ),
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="SlideClient created"
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
     * @param  SlideClientRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(SlideClientRequest $request)
    {
        $result = SlideClientService::create($request)
                                    ->getResult();

        return (new SlideClientResource($result))->additional(['message' => 'SlideClient created'])
                                                 ->response()
                                                 ->setStatusCode(201);
    }

    /**
     * @OA\Get (
     *   tags={"SlideClientsController"},
     *   path="/api/slide_clients/{slide_client}",
     *   summary="Get single slide_client",
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
     *     name="slide_client",
     *     parameter="slide_client",
     *     description="SlideClient id"
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *       @OA\Property(
     *         property="data",
     *         type="object",
     *         ref="#/components/schemas/SlideClientResource"
     *       ),
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="SlideClient read"
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
     * @param  SlideClient  $record
     * @return SlideClientResource
     */
    public function show(SlideClient $record)
    {
        $result = SlideClientService::show($record)
                                    ->getResult();

        return (new SlideClientResource($result))->additional(['message' => 'SlideClient read']);
    }

    /**
     * @OA\Put (
     *   tags={"SlideClientsController"},
     *   path="/api/slide_clients/{slide_client}",
     *   summary="Update an existing slide_client",
     *   @OA\RequestBody(
     *     @OA\JsonContent(ref="#/components/schemas/SlideClientRequest")
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
     *     name="slide_client",
     *     parameter="slide_client",
     *     description="SlideClient id"
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *       @OA\Property(
     *         property="data",
     *         type="object",
     *         ref="#/components/schemas/SlideClientResource"
     *       ),
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="SlideClient updated"
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
     * @param  SlideClientRequest  $request
     * @param  SlideClient  $record
     * @return SlideClientResource
     */
    public function update(SlideClientRequest $request, SlideClient $record)
    {
        $result = SlideClientService::update($record, $request)
                                    ->getResult();

        return (new SlideClientResource($result))->additional(['message' => 'SlideClient updated']);
    }

    /**
     * @OA\Delete (
     *   tags={"SlideClientsController"},
     *   path="/api/slide_clients/{slide_client}",
     *   summary="Delete a slide_client",
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
     *     name="slide_client",
     *     parameter="slide_client",
     *     description="SlideClient id"
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="SlideClient deleted"
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
     *         example="Problem deleting slide_client"
     *       )
     *     )
     *   )
     * )
     *
     * Remove the specified resource from storage.
     *
     * @param  SlideClient  $record
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(SlideClient $record)
    {
        $result = SlideClientService::delete($record)
                                    ->getResult();

        if ($result) {
            return response()->json(['message' => 'SlideClient deleted']);
        }

        return response()->json(['message' => 'Problem deleting SlideClient'], 404);
    }
}
