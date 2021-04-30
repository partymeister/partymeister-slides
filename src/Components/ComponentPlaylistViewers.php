<?php

namespace Partymeister\Slides\Components;

use Illuminate\Http\Request;
use Motor\CMS\Models\PageVersionComponent;
use Partymeister\Slides\Http\Resources\PlaylistResource;
use Partymeister\Slides\Models\Playlist;

class ComponentPlaylistViewers {

    protected $component;
    protected $pageVersionComponent;
    protected $playlist;

    public function __construct(PageVersionComponent $pageVersionComponent, \Partymeister\Slides\Models\Component\ComponentPlaylistViewer $component)
    {
        $this->component = $component;
        $this->pageVersionComponent = $pageVersionComponent;
    }

    public function index(Request $request)
    {
        $playlist = Playlist::find($this->component->playlist_id);
        $this->playlist = (new PlaylistResource($playlist->load('items')))->toArrayRecursive();

        return $this->render();
    }


    public function render()
    {
        return view(config('motor-cms-page-components.components.'.$this->pageVersionComponent->component_name.'.view'), ['component' => $this->component, 'playlist' => $this->playlist]);
    }

}
