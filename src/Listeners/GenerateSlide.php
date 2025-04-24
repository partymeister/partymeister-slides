<?php

namespace Partymeister\Slides\Listeners;

use Partymeister\Slides\Events\SlideSaved;

/**
 * Class GenerateSlide
 */
class GenerateSlide
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(SlideSaved $event)
    {
        \Partymeister\Slides\Jobs\GenerateSlide::dispatch($event->slide, $event->namePrefix);

        // foreach ($event->playlist->playlist_items as $item) {
        //    if ($item->slide_id != null) {
        //    }
        // }
    }
}
