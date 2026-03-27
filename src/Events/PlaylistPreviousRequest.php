<?php

namespace Partymeister\Slides\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class PlaylistPreviousRequest
 */
class PlaylistPreviousRequest implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var bool
     */
    public $hard = false;

    public ?int $clientId = null;

    /**
     * Create a new event instance.
     *
     * PlaylistPreviousRequest constructor.
     *
     * @param  bool  $hard
     */
    public function __construct($hard = false, ?int $clientId = null)
    {
        $this->hard = $hard;
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
