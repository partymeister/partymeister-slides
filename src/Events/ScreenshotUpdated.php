<?php

namespace Partymeister\Slides\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class PlaylistRequest
 */
class ScreenshotUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $slide;

    public function __construct(Model $slide)
    {
        $this->slide = new \stdClass;
        $this->slide->id = $slide->id;
        $this->slide->media = $slide->getFirstMedia('preview') ? $slide->getFirstMedia('preview') : '';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel(config('cache.prefix').'.slidemeister-web.screenshot-update');
    }
}
