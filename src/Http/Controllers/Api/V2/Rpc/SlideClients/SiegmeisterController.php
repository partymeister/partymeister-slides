<?php

namespace Partymeister\Slides\Http\Controllers\Api\V2\Rpc\SlideClients;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Partymeister\Slides\Events\SiegmeisterRequest;
use Partymeister\Slides\Models\SlideClient;

/**
 * @tags Slides: Slide Client Communication
 */
class SiegmeisterController extends Controller
{
    /**
     * @response array{data: array{message: string}, meta: array{api_version: string, message: string}}
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'slide_client_id' => 'required|integer|exists:slide_clients,id',
        ]);

        $client = SlideClient::findOrFail($request->slide_client_id);

        if ($client->type === 'slidemeister-web') {
            event(new SiegmeisterRequest($client->id));
        }

        return response()->json([
            'data' => ['message' => 'Siegmeister triggered'],
            'meta' => ['api_version' => 'v2', 'message' => 'Siegmeister dispatched'],
        ]);
    }
}
