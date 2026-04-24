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

    /**
     * @var
     */
    public $index;

    private ?int $slideClientId;

    public function __construct(Playlist $playlist, $index = false, ?int $slideClientId = null)
    {
        $this->playlist_id = $playlist->id;
        $this->index = $index;
        $this->slideClientId = $slideClientId;
    }

    public function broadcastOn(): array
    {
        $clientId = $this->slideClientId ?? session('screens.active');

        return [new Channel(config('cache.prefix').'.slidemeister-web.'.$clientId)];
    }
}
