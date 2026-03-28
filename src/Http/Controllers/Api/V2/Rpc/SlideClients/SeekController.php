<?php

namespace Partymeister\Slides\Http\Controllers\Api\V2\Rpc\SlideClients;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Partymeister\Slides\Events\PlaylistSeekRequest;
use Partymeister\Slides\Models\Playlist;
use Partymeister\Slides\Models\SlideClient;

/**
 * @tags Slides: Slide Client Communication
 */
class SeekController extends Controller
{
    /**
     * @response array{data: array{message: string}, meta: array{api_version: string, message: string}}
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'slide_client_id' => 'required|integer|exists:slide_clients,id',
            'playlist_id' => 'required|integer|exists:playlists,id',
            'continue' => 'sometimes|boolean',
        ]);

        $client = SlideClient::findOrFail($request->slide_client_id);
        $playlist = Playlist::findOrFail($request->playlist_id);
        $index = $request->boolean('continue') ? false : 0;

        if ($client->type === 'slidemeister-web') {
            event(new PlaylistSeekRequest($playlist, $index, $client->id));
        }

        return response()->json([
            'data' => ['message' => 'Seek command sent'],
            'meta' => ['api_version' => 'v2', 'message' => 'Seek dispatched'],
        ]);
    }
}
