<?php

use Partymeister\Slides\Models\Playlist;
use Partymeister\Slides\Models\Slide;
use Partymeister\Slides\Models\SlideClient;
use Partymeister\Slides\Models\SlideTemplate;
use Partymeister\Slides\Models\Transition;

function create_test_slide($count = 1)
{
    return factory(Slide::class, $count)->create();
}

function create_test_slide_template($count = 1)
{
    return factory(SlideTemplate::class, $count)->create();
}

function create_test_playlist($count = 1)
{
    return factory(Playlist::class, $count)->create();
}

function create_test_transition($count = 1)
{
    return factory(Transition::class, $count)->create();
}

function create_test_slide_client($count = 1)
{
    return factory(SlideClient::class, $count)->create();
}
