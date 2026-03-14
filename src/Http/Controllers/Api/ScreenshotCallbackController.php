<?php

namespace Partymeister\Slides\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Motor\Backend\Http\Controllers\ApiController;
use Partymeister\Slides\Events\ScreenshotUpdated;
use Partymeister\Slides\Models\Slide;
use Partymeister\Slides\Models\SlideTemplate;

class ScreenshotCallbackController extends ApiController
{
    protected array $allowedClasses = [
        Slide::class,
        SlideTemplate::class,
    ];

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'slideId'    => 'required|integer',
            'class'      => 'required|string',
            'fileName'   => 'required|string',
            'collection' => 'required|string|in:preview,final',
        ]);

        $class = $request->input('class');

        if (! in_array($class, $this->allowedClasses)) {
            return response()->json(['message' => 'Invalid model class'], 422);
        }

        $model = $class::find((int) $request->input('slideId'));

        if (! $model) {
            Log::warning('Screenshot callback: model not found', [
                'class'   => $class,
                'slideId' => $request->input('slideId'),
            ]);

            return response()->json(['message' => 'Model not found'], 404);
        }

        $fileName = $request->input('fileName');
        $collection = $request->input('collection');

        if (! is_file($fileName)) {
            Log::warning('Screenshot callback: file not found', ['fileName' => $fileName]);

            return response()->json(['message' => 'Screenshot file not found'], 404);
        }

        $model->clearMediaCollection($collection);

        try {
            $model->addMedia($fileName)
                  ->toMediaCollection($collection, 'media');
        } catch (\Exception $e) {
            Log::error('Screenshot callback: failed to attach media', [
                'slideId' => $model->id,
                'error'   => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Failed to attach media'], 500);
        }

        ScreenshotUpdated::dispatch($model);

        Log::info('Screenshot attached', [$model->id, $fileName, $collection]);

        return response()->json(['message' => 'Screenshot processed']);
    }
}
