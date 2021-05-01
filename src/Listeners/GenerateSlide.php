<?php

namespace Partymeister\Slides\Listeners;

use Partymeister\Slides\Events\SlideSaved;

/**
 * Class GenerateSlide
 *
 * @package Partymeister\Slides\Listeners
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
     * @param SlideSaved $event
     *
     * @return void
     */
    public function handle(SlideSaved $event)
    {
        \Partymeister\Slides\Jobs\GenerateSlide::dispatch($event->slide, $event->namePrefix);

        //foreach ($event->playlist->playlist_items as $item) {
        //    if ($item->slide_id != null) {
        //    }
        //}
    }
}
