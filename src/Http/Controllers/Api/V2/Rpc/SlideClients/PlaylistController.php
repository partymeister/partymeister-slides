<?php

namespace Partymeister\Slides\Http\Controllers\Api\V2\Rpc\SlideClients;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Partymeister\Slides\Events\PlaylistRequest;
use Partymeister\Slides\Models\Playlist;
use Partymeister\Slides\Models\SlideClient;

/**
 * @tags Slides: Slide Client Communication
 */
class PlaylistController extends Controller
{
    /**
     * @response array{data: array{message: string}, meta: array{api_version: string, message: string}}
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'slide_client_id' => 'required|integer|exists:slide_clients,id',
            'playlist_id' => 'required|integer|exists:playlists,id',
            'callbacks' => 'nullable',
        ]);

        $client = SlideClient::findOrFail($request->slide_client_id);
        $playlist = Playlist::with(['items.slide.media', 'items.transition', 'items.transition_slidemeister'])
            ->findOrFail($request->playlist_id);

        if ($client->type === 'slidemeister-web') {
            event(new PlaylistRequest($playlist, $request->get('callbacks'), $client->id));
        }

        return response()->json([
            'data' => ['message' => 'Playlist sent to client'],
            'meta' => ['api_version' => 'v2', 'message' => 'Playlist dispatched'],
        ]);
    }
}
