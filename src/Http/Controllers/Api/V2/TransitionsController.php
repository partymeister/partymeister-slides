<?php

namespace Partymeister\Slides\Http\Controllers\Api\V2;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Motor\Core\Http\Controllers\Api\V2\ApiController;
use Partymeister\Slides\Http\Requests\Api\V2\TransitionGetRequest;
use Partymeister\Slides\Http\Requests\Api\V2\TransitionPatchRequest;
use Partymeister\Slides\Http\Requests\Api\V2\TransitionPostRequest;
use Partymeister\Slides\Http\Resources\V2\TransitionCollection;
use Partymeister\Slides\Http\Resources\V2\TransitionResource;
use Partymeister\Slides\Models\Transition;
use Partymeister\Slides\Services\TransitionService;

class TransitionsController extends ApiController
{
    protected string $model = Transition::class;

    protected string $modelResource = 'transition';

    public function index(TransitionGetRequest $request): TransitionCollection
    {
        $paginator = TransitionService::collection()->getPaginator();

        return (new TransitionCollection($paginator))
            ->additional(['meta' => ['message' => 'Transitions retrieved']]);
    }

    public function store(TransitionPostRequest $request): JsonResponse
    {
        $result = TransitionService::create($request)->getResult();

        return (new TransitionResource($result))
            ->additional(['meta' => ['message' => 'Transition created']])
            ->response()->setStatusCode(201);
    }

    public function show(Transition $transition): TransitionResource
    {
        $result = TransitionService::show($transition)->getResult();

        return (new TransitionResource($result))
            ->additional(['meta' => ['message' => 'Transition retrieved']]);
    }

    public function update(TransitionPatchRequest $request, Transition $transition): TransitionResource
    {
        $result = TransitionService::update($transition, $request)->getResult();

        return (new TransitionResource($result))
            ->additional(['meta' => ['message' => 'Transition updated']]);
    }

    public function destroy(Transition $transition): Response
    {
        $result = TransitionService::delete($transition)->getResult();
        if ($result) {
            return $this->noContentResponse();
        }
        abort(404, 'Problem deleting transition');
    }
}
