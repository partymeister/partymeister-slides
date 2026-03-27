<?php

namespace Partymeister\Slides\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Motor\Media\Http\Resources\FileResource;
use Motor\Media\Models\File;
use Partymeister\Slides\Http\Resources\SlideResource;
use Partymeister\Slides\Models\Slide;

/**
 * Class PlayNowRequest
 */
class PlayNowRequest implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $item;

    public ?int $clientId = null;

    /**
     * Create a new event instance.
     *
     * PlayNowRequest constructor.
     */
    public function __construct($type, $item, ?int $clientId = null)
    {
        $this->clientId = $clientId;
        switch ($type) {
            case 'file':
                $file = File::find($item);
                $data = (new FileResource($file))->toArrayRecursive();
                $data['type'] = 'image';
                $data['playnow_type'] = 'file';
                $data['slide_type'] = 'default';
                if ($file->getFirstMedia('file')->mime_type == 'video/x-m4v' || $file->getFirstMedia('file')->mime_type == 'video/mp4') {
                    $data['type'] = 'video';
                }
                $this->item = $data;
                break;
            case 'slide':
                $file = Slide::find($item);
                $data = (new SlideResource($file))->toArrayRecursive();
                $data['type'] = 'image';
                $data['playnow_type'] = 'slide';
                $this->item = $data;
                break;
        }
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
