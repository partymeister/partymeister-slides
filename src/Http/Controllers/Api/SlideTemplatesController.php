<?php

namespace Partymeister\Slides\Http\Controllers\Api;

use Motor\Backend\Http\Controllers\ApiController;
use Partymeister\Slides\Http\Requests\Backend\SlideTemplateRequest;
use Partymeister\Slides\Http\Resources\SlideTemplateCollection;
use Partymeister\Slides\Http\Resources\SlideTemplateResource;
use Partymeister\Slides\Models\SlideTemplate;
use Partymeister\Slides\Services\SlideTemplateService;

/**
 * Class SlideTemplatesController
 *
 * @package Partymeister\Slides\Http\Controllers\Api
 */
class SlideTemplatesController extends ApiController
{
    protected string $model = 'Partymeister\Slides\Models\SlideTemplate';

    protected string $modelResource = 'slide_template';

    /**
     * @OA\Get (
     *   tags={"SlideTemplatesController"},
     *   path="/api/slide_templates",
     *   summary="Get slide_template collection",
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
     *         @OA\Items(ref="#/components/schemas/SlideTemplateResource")
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
     * @return SlideTemplateCollection
     */
    public function index()
    {
        $paginator = SlideTemplateService::collection()
                                         ->getPaginator();

        return (new SlideTemplateCollection($paginator))->additional(['message' => 'SlideTemplate collection read']);
    }

    /**
     * @OA\Post (
     *   tags={"SlideTemplatesController"},
     *   path="/api/slide_templates",
     *   summary="Create new slide_template",
     *   @OA\RequestBody(
     *     @OA\JsonContent(ref="#/components/schemas/SlideTemplateRequest")
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
     *         ref="#/components/schemas/SlideTemplateResource"
     *       ),
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="SlideTemplate created"
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
     * @param SlideTemplateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(SlideTemplateRequest $request)
    {
        $result = SlideTemplateService::create($request)
                                      ->getResult();

        return (new SlideTemplateResource($result))->additional(['message' => 'SlideTemplate created'])
                                                   ->response()
                                                   ->setStatusCode(201);
    }

    /**
     * @OA\Get (
     *   tags={"SlideTemplatesController"},
     *   path="/api/slide_templates/{slide_template}",
     *   summary="Get single slide_template",
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
     *     name="slide_template",
     *     parameter="slide_template",
     *     description="SlideTemplate id"
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *       @OA\Property(
     *         property="data",
     *         type="object",
     *         ref="#/components/schemas/SlideTemplateResource"
     *       ),
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="SlideTemplate read"
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
     * @param SlideTemplate $record
     * @return SlideTemplateResource
     */
    public function show(SlideTemplate $record)
    {
        $result = SlideTemplateService::show($record)
                                      ->getResult();

        return (new SlideTemplateResource($result))->additional(['message' => 'SlideTemplate read']);
    }

    /**
     * @OA\Put (
     *   tags={"SlideTemplatesController"},
     *   path="/api/slide_templates/{slide_template}",
     *   summary="Update an existing slide_template",
     *   @OA\RequestBody(
     *     @OA\JsonContent(ref="#/components/schemas/SlideTemplateRequest")
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
     *     name="slide_template",
     *     parameter="slide_template",
     *     description="SlideTemplate id"
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *       @OA\Property(
     *         property="data",
     *         type="object",
     *         ref="#/components/schemas/SlideTemplateResource"
     *       ),
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="SlideTemplate updated"
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
     * @param SlideTemplateRequest $request
     * @param SlideTemplate $record
     * @return SlideTemplateResource
     */
    public function update(SlideTemplateRequest $request, SlideTemplate $record)
    {
        $result = SlideTemplateService::update($record, $request)
                                      ->getResult();

        return (new SlideTemplateResource($result))->additional(['message' => 'SlideTemplate updated']);
    }

    /**
     * @OA\Delete (
     *   tags={"SlideTemplatesController"},
     *   path="/api/slide_templates/{slide_template}",
     *   summary="Delete a slide_template",
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
     *     name="slide_template",
     *     parameter="slide_template",
     *     description="SlideTemplate id"
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="SlideTemplate deleted"
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
     *         example="Problem deleting slide_template"
     *       )
     *     )
     *   )
     * )
     *
     * Remove the specified resource from storage.
     *
     * @param SlideTemplate $record
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(SlideTemplate $record)
    {
        $result = SlideTemplateService::delete($record)
                                      ->getResult();

        if ($result) {
            return response()->json(['message' => 'SlideTemplate deleted']);
        }

        return response()->json(['message' => 'Problem deleting SlideTemplate'], 404);
    }
}
