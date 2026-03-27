<?php

namespace Partymeister\Slides\Http\Controllers\Api\V2\Rpc\SlideClients;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Partymeister\Slides\Events\PlaylistNextRequest;
use Partymeister\Slides\Events\PlaylistPreviousRequest;
use Partymeister\Slides\Models\SlideClient;

class SkipController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'slide_client_id' => 'required|integer|exists:slide_clients,id',
            'direction' => 'required|in:next,previous',
            'hard' => 'sometimes|boolean',
        ]);

        $client = SlideClient::findOrFail($request->slide_client_id);
        $hard = $request->boolean('hard', false);

        if ($client->type === 'slidemeister-web') {
            $event = $request->direction === 'next'
                ? new PlaylistNextRequest($hard, $client->id)
                : new PlaylistPreviousRequest($hard, $client->id);
            event($event);
        }

        return response()->json([
            'data' => ['message' => 'Skip command sent'],
            'meta' => ['api_version' => 'v2', 'message' => ucfirst($request->direction).' dispatched'],
        ]);
    }
}
