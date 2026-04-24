<?php

namespace Partymeister\Slides\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class PlaylistNextRequest
 */
class SiegmeisterRequest implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private ?int $slideClientId;

    public function __construct(?int $slideClientId = null)
    {
        $this->slideClientId = $slideClientId;
    }

    public function broadcastOn(): array
    {
        $clientId = $this->slideClientId ?? session('screens.active');

        return [new Channel(config('cache.prefix').'.slidemeister-web.'.$clientId)];
    }
}
