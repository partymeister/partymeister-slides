<?php

namespace Partymeister\Slides\Http\Controllers\Api;

use Motor\Backend\Http\Controllers\ApiController;

use Partymeister\Slides\Models\Transition;
use Partymeister\Slides\Http\Requests\Backend\TransitionRequest;
use Partymeister\Slides\Services\TransitionService;
use Partymeister\Slides\Http\Resources\TransitionResource;
use Partymeister\Slides\Http\Resources\TransitionCollection;

/**
 * Class TransitionsController
 * @package Partymeister\Slides\Http\Controllers\Api
 */
class TransitionsController extends ApiController
{

    protected string $modelResource = 'transition';

    /**
     * @OA\Get (
     *   tags={"TransitionsController"},
     *   path="/api/transitions",
     *   summary="Get transition collection",
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
     *         @OA\Items(ref="#/components/schemas/TransitionResource")
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
     * @return TransitionCollection
     */
    public function index()
    {
        $paginator = TransitionService::collection()->getPaginator();
        return (new TransitionCollection($paginator))->additional(['message' => 'Transition collection read']);
    }

    /**
     * @OA\Post (
     *   tags={"TransitionsController"},
     *   path="/api/transitions",
     *   summary="Create new transition",
     *   @OA\RequestBody(
     *     @OA\JsonContent(ref="#/components/schemas/TransitionRequest")
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
     *         ref="#/components/schemas/TransitionResource"
     *       ),
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="Transition created"
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
     * @param TransitionRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(TransitionRequest $request)
    {
        $result = TransitionService::create($request)->getResult();
        return (new TransitionResource($result))->additional(['message' => 'Transition created'])->response()->setStatusCode(201);
    }


    /**
     * @OA\Get (
     *   tags={"TransitionsController"},
     *   path="/api/transitions/{transition}",
     *   summary="Get single transition",
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
     *     name="transition",
     *     parameter="transition",
     *     description="Transition id"
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *       @OA\Property(
     *         property="data",
     *         type="object",
     *         ref="#/components/schemas/TransitionResource"
     *       ),
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="Transition read"
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
     * @param Transition $record
     * @return TransitionResource
     */
    public function show(Transition $record)
    {
        $result = TransitionService::show($record)->getResult();
        return (new TransitionResource($result))->additional(['message' => 'Transition read']);
    }


    /**
     * @OA\Put (
     *   tags={"TransitionsController"},
     *   path="/api/transitions/{transition}",
     *   summary="Update an existing transition",
     *   @OA\RequestBody(
     *     @OA\JsonContent(ref="#/components/schemas/TransitionRequest")
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
     *     name="transition",
     *     parameter="transition",
     *     description="Transition id"
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *       @OA\Property(
     *         property="data",
     *         type="object",
     *         ref="#/components/schemas/TransitionResource"
     *       ),
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="Transition updated"
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
     * @param TransitionRequest $request
     * @param Transition        $record
     * @return TransitionResource
     */
    public function update(TransitionRequest $request, Transition $record)
    {
        $result = TransitionService::update($record, $request)->getResult();
        return (new TransitionResource($result))->additional(['message' => 'Transition updated']);
    }


    /**
     * @OA\Delete (
     *   tags={"TransitionsController"},
     *   path="/api/transitions/{transition}",
     *   summary="Delete a transition",
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
     *     name="transition",
     *     parameter="transition",
     *     description="Transition id"
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="Transition deleted"
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
     *         example="Problem deleting transition"
     *       )
     *     )
     *   )
     * )
     *
     * Remove the specified resource from storage.
     *
     * @param Transition $record
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Transition $record)
    {
        $result = TransitionService::delete($record)->getResult();

        if ($result) {
            return response()->json(['message' => 'Transition deleted']);
        }
        return response()->json(['message' => 'Problem deleting Transition'], 404);
    }
}
