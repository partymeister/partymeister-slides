<?php

namespace Partymeister\Slides\Http\Controllers\Api\V2;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Motor\Core\Http\Controllers\Api\V2\ApiController;
use Partymeister\Slides\Http\Requests\Api\V2\SlideClientGetRequest;
use Partymeister\Slides\Http\Requests\Api\V2\SlideClientPatchRequest;
use Partymeister\Slides\Http\Requests\Api\V2\SlideClientPostRequest;
use Partymeister\Slides\Http\Resources\V2\SlideClientCollection;
use Partymeister\Slides\Http\Resources\V2\SlideClientResource;
use Partymeister\Slides\Models\SlideClient;
use Partymeister\Slides\Services\SlideClientService;

/**
 * @tags Slide Clients
 */
class SlideClientsController extends ApiController
{
    protected string $model = SlideClient::class;

    protected string $modelResource = 'slide_client';

    /**
     * @response Illuminate\Http\Resources\Json\AnonymousResourceCollection<Illuminate\Pagination\LengthAwarePaginator<SlideClientResource>>
     */
    public function index(SlideClientGetRequest $request): SlideClientCollection
    {
        $paginator = SlideClientService::collection()->getPaginator();

        return (new SlideClientCollection($paginator))
            ->additional(['meta' => ['message' => 'Slide clients retrieved']]);
    }

    /** @response 201 SlideClientResource */
    public function store(SlideClientPostRequest $request): JsonResponse
    {
        $result = SlideClientService::create($request)->getResult();

        return (new SlideClientResource($result))
            ->additional(['meta' => ['message' => 'Slide client created']])
            ->response()->setStatusCode(201);
    }

    /** @response SlideClientResource */
    public function show(SlideClient $slideClient): SlideClientResource
    {
        $result = SlideClientService::show($slideClient)->getResult();

        return (new SlideClientResource($result))
            ->additional(['meta' => ['message' => 'Slide client retrieved']]);
    }

    /** @response SlideClientResource */
    public function update(SlideClientPatchRequest $request, SlideClient $slideClient): SlideClientResource
    {
        $result = SlideClientService::update($slideClient, $request)->getResult();

        return (new SlideClientResource($result))
            ->additional(['meta' => ['message' => 'Slide client updated']]);
    }

    /** @response 204 */
    public function destroy(SlideClient $slideClient): Response
    {
        $result = SlideClientService::delete($slideClient)->getResult();
        if ($result) {
            return $this->noContentResponse();
        }
        abort(404, 'Problem deleting slide client');
    }
}
