<?php

namespace Partymeister\Slides\Http\Controllers\Api;

use Motor\Backend\Http\Controllers\ApiController;
use Partymeister\Slides\Http\Requests\Backend\SlideRequest;
use Partymeister\Slides\Http\Resources\SlideCollection;
use Partymeister\Slides\Http\Resources\SlideResource;
use Partymeister\Slides\Models\Slide;
use Partymeister\Slides\Services\SlideService;

/**
 * Class SlidesController
 */
class SlidesController extends ApiController
{
    protected string $model = 'Partymeister\Slides\Models\Slide';

    protected string $modelResource = 'slide';

    /**
     * @OA\Get (
     *   tags={"SlidesController"},
     *   path="/api/slides",
     *   summary="Get slide collection",
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
     *         @OA\Items(ref="#/components/schemas/SlideResource")
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
     * @return SlideCollection
     */
    public function index()
    {
        $paginator = SlideService::collection()
            ->getPaginator();

        return (new SlideCollection($paginator))->additional(['message' => 'Slide collection read']);
    }

    /**
     * @OA\Post (
     *   tags={"SlidesController"},
     *   path="/api/slides",
     *   summary="Create new slide",
     *
     *   @OA\RequestBody(
     *
     *     @OA\JsonContent(ref="#/components/schemas/SlideRequest")
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
     *         ref="#/components/schemas/SlideResource"
     *       ),
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="Slide created"
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
    public function store(SlideRequest $request)
    {
        $result = SlideService::create($request)
            ->getResult();

        return (new SlideResource($result))->additional(['message' => 'Slide created'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @OA\Get (
     *   tags={"SlidesController"},
     *   path="/api/slides/{slide}",
     *   summary="Get single slide",
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
     *     name="slide",
     *     parameter="slide",
     *     description="Slide id"
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
     *         ref="#/components/schemas/SlideResource"
     *       ),
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="Slide read"
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
     * @return SlideResource
     */
    public function show(Slide $record)
    {
        $result = SlideService::show($record)
            ->getResult();

        return (new SlideResource($result))->additional(['message' => 'Slide read']);
    }

    /**
     * @OA\Put (
     *   tags={"SlidesController"},
     *   path="/api/slides/{slide}",
     *   summary="Update an existing slide",
     *
     *   @OA\RequestBody(
     *
     *     @OA\JsonContent(ref="#/components/schemas/SlideRequest")
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
     *     name="slide",
     *     parameter="slide",
     *     description="Slide id"
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
     *         ref="#/components/schemas/SlideResource"
     *       ),
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="Slide updated"
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
     * @return SlideResource
     */
    public function update(SlideRequest $request, Slide $record)
    {
        $result = SlideService::update($record, $request)
            ->getResult();

        return (new SlideResource($result))->additional(['message' => 'Slide updated']);
    }

    /**
     * @OA\Delete (
     *   tags={"SlidesController"},
     *   path="/api/slides/{slide}",
     *   summary="Delete a slide",
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
     *     name="slide",
     *     parameter="slide",
     *     description="Slide id"
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
     *         example="Slide deleted"
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
     *         example="Problem deleting slide"
     *       )
     *     )
     *   )
     * )
     *
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Slide $record)
    {
        $result = SlideService::delete($record)
            ->getResult();

        if ($result) {
            return response()->json(['message' => 'Slide deleted']);
        }

        return response()->json(['message' => 'Problem deleting Slide'], 404);
    }
}
