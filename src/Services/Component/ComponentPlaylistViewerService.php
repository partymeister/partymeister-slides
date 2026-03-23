<?php

namespace Partymeister\Slides\Services\Component;

use Motor\CMS\Services\ComponentBaseService;
use Partymeister\Slides\Models\Component\ComponentPlaylistViewer;

class ComponentPlaylistViewerService extends ComponentBaseService
{
    protected string $model = ComponentPlaylistViewer::class;

    protected $name = 'playlist-viewers';
}
