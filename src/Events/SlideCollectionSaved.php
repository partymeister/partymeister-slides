<?php

namespace Partymeister\Slides\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/**
 * Class SlideCollectionSaved
 */
class SlideCollectionSaved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Collection
     */
    public $slideIds;

    /**
     * @var string
     */
    public $namePrefix = '';

    /**
     * Create a new event instance.
     *
     * SlideCollectionSaved constructor.
     */
    public function __construct(Collection $slideIds, $namePrefix)
    {
        $this->slideIds = $slideIds;
        $this->namePrefix = $namePrefix;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
