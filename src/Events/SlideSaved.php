<?php

namespace Partymeister\Slides\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class SlideSaved
 */
class SlideSaved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Model
     */
    public $slide;

    /**
     * @var string
     */
    public $namePrefix = '';

    /**
     * Create a new event instance.
     *
     * SlideSaved constructor.
     *
     * @param  Model  $slide
     * @param    $namePrefix
     */
    public function __construct(Model $slide, $namePrefix)
    {
        $this->slide = $slide;
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
