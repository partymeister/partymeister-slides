<?php

namespace Partymeister\Slides\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Partymeister\Slides\Models\Playlist;

/**
 * Class PlaylistSeekRequest
 */
class PlaylistSeekRequest implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var int
     */
    public $playlist_id;

    public $index;

    /**
     * Create a new event instance.
     *
     * PlaylistSeekRequest constructor.
     */
    public function __construct(Playlist $playlist, $index = false)
    {
        $this->playlist_id = $playlist->id;
        $this->index = $index;
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
