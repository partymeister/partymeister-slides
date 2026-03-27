<?php

namespace Partymeister\Slides\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Partymeister\Slides\Models\Playlist;

/**
 * Class PlaylistRequest
 */
class PlaylistRequest implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $playlist_id;

    public $callbacks;

    public string $callback_url;

    public ?int $clientId = null;

    /**
     * Create a new event instance.
     */
    public function __construct(Playlist $playlist, $callbacks, ?int $clientId = null)
    {
        $this->playlist_id = $playlist->id;
        $this->callbacks = $callbacks;
        $this->callback_url = config('app.url').'/api-rpc/callback/';
        $this->clientId = $clientId;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $activeClient = $this->clientId ?? session('screens.active');

        return [new Channel(config('cache.prefix').'.slidemeister-web.'.$activeClient)];
    }
}
