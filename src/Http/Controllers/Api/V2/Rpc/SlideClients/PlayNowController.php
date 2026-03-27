<?php

namespace Partymeister\Slides\Http\Controllers\Api\V2\Rpc\SlideClients;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Partymeister\Slides\Events\PlayNowRequest;
use Partymeister\Slides\Models\SlideClient;

class PlayNowController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'slide_client_id' => 'required|integer|exists:slide_clients,id',
            'type' => 'required|in:slide,file',
            'item' => 'required|integer',
        ]);

        $client = SlideClient::findOrFail($request->slide_client_id);

        if ($client->type === 'slidemeister-web') {
            event(new PlayNowRequest($request->type, $request->item, $client->id));
        }

        return response()->json([
            'data' => ['message' => 'Play now command sent'],
            'meta' => ['api_version' => 'v2', 'message' => 'PlayNow dispatched'],
        ]);
    }
}
