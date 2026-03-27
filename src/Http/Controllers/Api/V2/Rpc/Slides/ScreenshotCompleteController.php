<?php

namespace Partymeister\Slides\Http\Controllers\Api\V2\Rpc\Slides;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Partymeister\Slides\Events\ScreenshotUpdated;
use Partymeister\Slides\Models\Slide;
use Partymeister\Slides\Models\SlideTemplate;

/**
 * @tags Screenshots
 */
class ScreenshotCompleteController extends Controller
{
    private array $allowedClasses = [
        Slide::class,
        SlideTemplate::class,
    ];

    /**
     * @response array{data: array{message: string}, meta: array{api_version: string, message: string}}
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'slideId' => 'required|integer',
            'class' => 'required|string',
            'fileName' => 'required|string',
            'collection' => 'required|string|in:preview,final',
        ]);

        $modelClass = $request->get('class');
        if (! in_array($modelClass, $this->allowedClasses)) {
            abort(422, 'Invalid model class');
        }

        $model = $modelClass::find((int) $request->get('slideId'));
        if (! $model) {
            Log::warning('Screenshot callback: model not found', [
                'class' => $modelClass,
                'slideId' => $request->get('slideId'),
            ]);

            return response()->json([
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Model not found'],
                'meta' => ['api_version' => 'v2'],
            ], 404);
        }

        $fileName = $request->get('fileName');
        $collection = $request->get('collection');

        // Security: path traversal protection
        $realPath = realpath($fileName);
        if (! $realPath || ! str_starts_with($realPath, storage_path().'/')) {
            Log::warning('Screenshot callback: path outside storage directory', ['fileName' => $fileName]);
            abort(422, 'Invalid file path');
        }

        // Security: extension check
        if (strtolower(pathinfo($realPath, PATHINFO_EXTENSION)) !== 'png') {
            Log::warning('Screenshot callback: non-PNG file rejected', ['fileName' => $fileName]);
            abort(422, 'Invalid file extension');
        }

        // Security: MIME type check
        $mimeType = mime_content_type($realPath);
        if ($mimeType !== 'image/png') {
            Log::warning('Screenshot callback: invalid MIME type', ['fileName' => $fileName, 'mime' => $mimeType]);
            abort(422, 'Invalid file type');
        }

        try {
            $model->clearMediaCollection($collection);
            $model->addMedia($realPath)
                ->toMediaCollection($collection, 'media');
        } catch (\Throwable $e) {
            Log::error('Screenshot callback: failed to attach media', [
                'slideId' => $model->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => ['code' => 'PROCESSING_ERROR', 'message' => 'Failed to attach media'],
                'meta' => ['api_version' => 'v2'],
            ], 500);
        }

        ScreenshotUpdated::dispatch($model);

        Log::info('Screenshot attached', [$model->id, $fileName, $collection]);

        return response()->json([
            'data' => ['message' => 'Screenshot processed'],
            'meta' => ['api_version' => 'v2', 'message' => 'Screenshot callback complete'],
        ]);
    }
}
