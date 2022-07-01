<?php

namespace Partymeister\Slides\Listeners;

use Partymeister\Slides\Events\SlideCollectionSaved;

/**
 * Class GenerateSlideCollection
 */
class GenerateSlideCollection
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
     * @param  SlideCollectionSaved  $event
     */
    public function handle(SlideCollectionSaved $event)
    {
        \Partymeister\Slides\Jobs\GenerateSlideCollection::dispatch($event->slideIds, $event->namePrefix);

        //foreach ($event->playlist->playlist_items as $item) {
        //    if ($item->slide_id != null) {
        //    }
        //}
    }
}
