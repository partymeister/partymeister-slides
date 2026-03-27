<?php

namespace Partymeister\Slides\Http\Controllers\Api\V2;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Motor\Core\Http\Controllers\Api\V2\ApiController;
use Partymeister\Slides\Http\Requests\Api\V2\SlideGetRequest;
use Partymeister\Slides\Http\Requests\Api\V2\SlidePatchRequest;
use Partymeister\Slides\Http\Requests\Api\V2\SlidePostRequest;
use Partymeister\Slides\Http\Resources\V2\SlideCollection;
use Partymeister\Slides\Http\Resources\V2\SlideResource;
use Partymeister\Slides\Models\Slide;
use Partymeister\Slides\Services\SlideService;

/**
 * @tags Slides
 */
class SlidesController extends ApiController
{
    protected string $model = Slide::class;

    protected string $modelResource = 'slide';

    /**
     * @response Illuminate\Http\Resources\Json\AnonymousResourceCollection<Illuminate\Pagination\LengthAwarePaginator<SlideResource>>
     */
    public function index(SlideGetRequest $request): SlideCollection
    {
        $paginator = SlideService::collection()->getPaginator();

        return (new SlideCollection($paginator))
            ->additional(['meta' => ['message' => 'Slides retrieved']]);
    }

    /** @response 201 SlideResource */
    public function store(SlidePostRequest $request): JsonResponse
    {
        $result = SlideService::create($request)->getResult();

        return (new SlideResource($result))
            ->additional(['meta' => ['message' => 'Slide created']])
            ->response()->setStatusCode(201);
    }

    /** @response SlideResource */
    public function show(Slide $slide): SlideResource
    {
        $result = SlideService::show($slide)->getResult();

        return (new SlideResource($result))
            ->additional(['meta' => ['message' => 'Slide retrieved']]);
    }

    /** @response SlideResource */
    public function update(SlidePatchRequest $request, Slide $slide): SlideResource
    {
        $result = SlideService::update($slide, $request)->getResult();

        return (new SlideResource($result))
            ->additional(['meta' => ['message' => 'Slide updated']]);
    }

    /** @response 204 */
    public function destroy(Slide $slide): Response
    {
        $result = SlideService::delete($slide)->getResult();
        if ($result) {
            return $this->noContentResponse();
        }
        abort(404, 'Problem deleting slide');
    }
}
