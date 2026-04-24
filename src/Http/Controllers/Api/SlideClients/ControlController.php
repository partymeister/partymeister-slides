<?php

namespace Partymeister\Slides\Http\Controllers\Api\SlideClients;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Motor\Backend\Http\Controllers\Controller;
use Partymeister\Slides\Events\PlaylistNextRequest;
use Partymeister\Slides\Events\PlaylistPreviousRequest;
use Partymeister\Slides\Events\PlaylistRequest;
use Partymeister\Slides\Events\PlaylistSeekRequest;
use Partymeister\Slides\Events\PlayNowRequest;
use Partymeister\Slides\Events\SiegmeisterRequest;
use Partymeister\Slides\Http\Resources\PlaylistCollection;
use Partymeister\Slides\Http\Resources\PlaylistResource;
use Partymeister\Slides\Http\Resources\SlideClientCollection;
use Partymeister\Slides\Http\Resources\SlideCollection;
use Partymeister\Slides\Http\Resources\SlideResource;
use Partymeister\Slides\Models\Playlist;
use Partymeister\Slides\Models\Slide;
use Partymeister\Slides\Models\SlideClient;

/**
 * External control API for the slidemeister-web viewer.
 *
 * Stateless counterpart to CommunicationController: the SlideClient is taken
 * from the route binding (not session('screens.active')), authentication is
 * the shared X-Slidemeister-Token header, and only slidemeister-web clients
 * are supported (no XML/screens branch).
 */
class ControlController extends Controller
{
    public function playlist(Request $request, SlideClient $slideClient): JsonResponse
    {
        if (! $this->isWebClient($slideClient)) {
            return $this->rejectNonWebClient();
        }

        $data = $request->validate([
            'playlist_id' => 'required|integer',
            'callbacks' => 'nullable',
        ]);

        $playlist = Playlist::find($data['playlist_id']);
        if (! $playlist) {
            return response()->json(['message' => 'Playlist not found'], 404);
        }

        event(new PlaylistRequest($playlist, $data['callbacks'] ?? null, $slideClient->id));

        return response()->json(['result' => 'Playlist event sent']);
    }

    public function playnow(Request $request, SlideClient $slideClient): JsonResponse
    {
        if (! $this->isWebClient($slideClient)) {
            return $this->rejectNonWebClient();
        }

        $data = $request->validate([
            'file_id' => 'nullable|integer',
            'slide_id' => 'nullable|integer',
        ]);

        if (empty($data['file_id']) && empty($data['slide_id'])) {
            return response()->json(['message' => 'Either file_id or slide_id is required'], 422);
        }

        if (! empty($data['file_id'])) {
            $type = 'file';
            $item = $data['file_id'];
        } else {
            $type = 'slide';
            $item = $data['slide_id'];
        }

        event(new PlayNowRequest($type, $item, $slideClient->id));

        return response()->json(['result' => 'PlayNow event sent']);
    }

    public function seek(Request $request, SlideClient $slideClient): JsonResponse
    {
        return $this->dispatchSeek($request, $slideClient, fromStart: true);
    }

    public function seekContinue(Request $request, SlideClient $slideClient): JsonResponse
    {
        return $this->dispatchSeek($request, $slideClient, fromStart: false);
    }

    public function skip(Request $request, SlideClient $slideClient): JsonResponse
    {
        if (! $this->isWebClient($slideClient)) {
            return $this->rejectNonWebClient();
        }

        $data = $request->validate([
            'direction' => 'required|in:next,previous',
            'hard' => 'nullable|boolean',
        ]);

        $hard = (bool) ($data['hard'] ?? false);

        match ($data['direction']) {
            'next' => event(new PlaylistNextRequest($hard, $slideClient->id)),
            'previous' => event(new PlaylistPreviousRequest($hard, $slideClient->id)),
        };

        return response()->json(['result' => 'Skip event sent']);
    }

    public function siegmeister(Request $request, SlideClient $slideClient): JsonResponse
    {
        if (! $this->isWebClient($slideClient)) {
            return $this->rejectNonWebClient();
        }

        event(new SiegmeisterRequest($slideClient->id));

        return response()->json(['result' => 'Siegmeister event sent']);
    }

    public function state(Request $request, SlideClient $slideClient): JsonResponse
    {
        if (! $this->isWebClient($slideClient)) {
            return $this->rejectNonWebClient();
        }

        $cached = Cache::store('redis')
            ->get(config('cache.prefix').':slidemeister-web.'.$slideClient->id);

        return response()->json([
            'data' => [
                'slide_client_id' => $slideClient->id,
                'state' => $cached ?: null,
            ],
        ]);
    }

    public function slideClients(): JsonResponse
    {
        $clients = SlideClient::where('type', 'slidemeister-web')->orderBy('name')->get();

        return (new SlideClientCollection($clients))->response();
    }

    public function playlists(Request $request): JsonResponse
    {
        $playlists = Playlist::orderBy('name')->paginate(
            perPage: (int) $request->query('per_page', 100),
        );

        return (new PlaylistCollection($playlists))->response();
    }

    public function showPlaylist(Playlist $playlist): JsonResponse
    {
        return (new PlaylistResource($playlist))->response();
    }

    public function slides(Request $request): JsonResponse
    {
        $slides = Slide::orderBy('name')->paginate(
            perPage: (int) $request->query('per_page', 100),
        );

        return (new SlideCollection($slides))->response();
    }

    public function showSlide(Slide $slide): JsonResponse
    {
        return (new SlideResource($slide))->response();
    }

    private function dispatchSeek(Request $request, SlideClient $slideClient, bool $fromStart): JsonResponse
    {
        if (! $this->isWebClient($slideClient)) {
            return $this->rejectNonWebClient();
        }

        $data = $request->validate([
            'playlist_id' => 'required|integer',
        ]);

        $playlist = Playlist::find($data['playlist_id']);
        if (! $playlist) {
            return response()->json(['message' => 'Playlist not found'], 404);
        }

        $index = $fromStart ? 0 : false;

        event(new PlaylistSeekRequest($playlist, $index, $slideClient->id));

        return response()->json(['result' => 'Seek event sent']);
    }

    private function isWebClient(SlideClient $slideClient): bool
    {
        return $slideClient->type === 'slidemeister-web';
    }

    private function rejectNonWebClient(): JsonResponse
    {
        return response()->json(
            ['message' => 'Control API only supports slidemeister-web slide clients'],
            422,
        );
    }
}
