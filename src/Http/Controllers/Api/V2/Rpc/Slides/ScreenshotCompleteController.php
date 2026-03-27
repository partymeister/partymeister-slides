<?php

namespace Partymeister\Slides\Http\Controllers\Api\V2\Rpc\Slides;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Partymeister\Slides\Events\ScreenshotUpdated;
use Partymeister\Slides\Models\Slide;
use Partymeister\Slides\Models\SlideTemplate;

class ScreenshotCompleteController extends Controller
{
    private array $allowedClasses = [
        Slide::class,
        SlideTemplate::class,
    ];

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

        $fileName = $request->get('fileName');

        // Security: path traversal protection
        $realPath = realpath($fileName);
        if (! $realPath || ! str_starts_with($realPath, storage_path().'/')) {
            abort(422, 'Invalid file path');
        }

        // Security: extension check
        if (pathinfo($realPath, PATHINFO_EXTENSION) !== 'png') {
            abort(422, 'Invalid file extension');
        }

        // Security: MIME type check
        $mimeType = mime_content_type($realPath);
        if ($mimeType !== 'image/png') {
            abort(422, 'Invalid file type');
        }

        $model = $modelClass::findOrFail((int) $request->get('slideId'));

        try {
            $model->clearMediaCollection($request->get('collection'));
            $model->addMedia($realPath)
                ->toMediaCollection($request->get('collection'), 'media');

            ScreenshotUpdated::dispatch($model);
        } catch (\Throwable $e) {
            \Log::error('Screenshot processing failed: '.$e->getMessage());
        }

        return response()->json([
            'data' => ['message' => 'Screenshot processed'],
            'meta' => ['api_version' => 'v2', 'message' => 'Screenshot callback complete'],
        ]);
    }
}
