<?php

namespace Partymeister\Slides\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Partymeister\Slides\Http\Resources\PlaylistResource;
use Partymeister\Slides\Models\Playlist;

/**
 * Class PlaylistRequest
 *
 * @package Partymeister\Slides\Events
 */
class PlaylistRequest implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var
     */
    public $playlist;

    /**
     * Create a new event instance.
     *
     * PlaylistRequest constructor.
     *
     * @param Playlist $playlist
     * @param          $callbacks
     */
    public function __construct(Playlist $playlist, $callbacks)
    {
        $this->playlist = (new PlaylistResource($playlist->load('items')))->toArrayRecursive();
        $this->playlist['callbacks'] = $callbacks;
        $this->playlist['callback_url'] = config('app.url').'/api/callback/';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel(config('cache.prefix').'.slidemeister-web.'.session('screens.active'));
    }
}
