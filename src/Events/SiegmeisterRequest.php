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

    public ?int $clientId = null;

    /**
     * Create a new event instance.
     *
     * PlaylistNextRequest constructor.
     */
    public function __construct(?int $clientId = null)
    {
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
