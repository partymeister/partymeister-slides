<?php

namespace Partymeister\Slides\Http\Controllers\Api\V2;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Motor\Core\Http\Controllers\Api\V2\ApiController;
use Partymeister\Slides\Http\Requests\Api\V2\SlideTemplateGetRequest;
use Partymeister\Slides\Http\Requests\Api\V2\SlideTemplatePatchRequest;
use Partymeister\Slides\Http\Requests\Api\V2\SlideTemplatePostRequest;
use Partymeister\Slides\Http\Resources\V2\SlideTemplateCollection;
use Partymeister\Slides\Http\Resources\V2\SlideTemplateResource;
use Partymeister\Slides\Models\SlideTemplate;
use Partymeister\Slides\Services\SlideTemplateService;

/**
 * @tags Slide Templates
 */
class SlideTemplatesController extends ApiController
{
    protected string $model = SlideTemplate::class;

    protected string $modelResource = 'slide_template';

    /**
     * @response Illuminate\Http\Resources\Json\AnonymousResourceCollection<Illuminate\Pagination\LengthAwarePaginator<SlideTemplateResource>>
     */
    public function index(SlideTemplateGetRequest $request): SlideTemplateCollection
    {
        $paginator = SlideTemplateService::collection()->getPaginator();

        return (new SlideTemplateCollection($paginator))
            ->additional(['meta' => ['message' => 'SlideTemplates retrieved']]);
    }

    /** @response 201 SlideTemplateResource */
    public function store(SlideTemplatePostRequest $request): JsonResponse
    {
        $result = SlideTemplateService::create($request)->getResult();

        return (new SlideTemplateResource($result))
            ->additional(['meta' => ['message' => 'SlideTemplate created']])
            ->response()->setStatusCode(201);
    }

    /** @response SlideTemplateResource */
    public function show(SlideTemplate $slideTemplate): SlideTemplateResource
    {
        $result = SlideTemplateService::show($slideTemplate)->getResult();

        return (new SlideTemplateResource($result))
            ->additional(['meta' => ['message' => 'SlideTemplate retrieved']]);
    }

    /** @response SlideTemplateResource */
    public function update(SlideTemplatePatchRequest $request, SlideTemplate $slideTemplate): SlideTemplateResource
    {
        $result = SlideTemplateService::update($slideTemplate, $request)->getResult();

        return (new SlideTemplateResource($result))
            ->additional(['meta' => ['message' => 'SlideTemplate updated']]);
    }

    /** @response 204 */
    public function destroy(SlideTemplate $slideTemplate): Response
    {
        $result = SlideTemplateService::delete($slideTemplate)->getResult();
        if ($result) {
            return $this->noContentResponse();
        }
        abort(404, 'Problem deleting slide template');
    }
}
