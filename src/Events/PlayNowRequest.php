<?php

namespace Partymeister\Slides\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Motor\Media\Http\Resources\FileResource;
use Motor\Media\Models\File;
use Partymeister\Slides\Models\Slide;

/**
 * Class PlayNowRequest
 */
class PlayNowRequest implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var
     */
    public $item;

    /**
     * Create a new event instance.
     *
     * PlayNowRequest constructor.
     *
     * @param $type
     * @param $item
     */
    public function __construct($type, $item)
    {
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
                $slide = Slide::find($item);
                $this->item = [
                    'type' => 'image',
                    'playnow_type' => 'slide',
                    'slide_type' => $slide->slide_type,
                    'cached_html_final' => $slide->cached_html_final,
                ];
                break;
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array
     */
    public function broadcastOn(): array
    {
        return [new Channel(config('cache.prefix').'.slidemeister-web.'.session('screens.active'))];
    }
}
