<?php

namespace Partymeister\Slides\Models\Component;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Motor\CMS\Models\ComponentBaseModel;

class ComponentPlaylistViewer extends ComponentBaseModel
{

    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'playlist_id',
    ];

    /**
     * Preview function for the page editor
     *
     * @return mixed
     */
    public function preview()
    {
        return [
            'name'    => trans('partymeister-slides::component/playlist-viewers.component'),
            'preview' => 'Preview for ComponentPlaylistViewer component',
        ];
    }
}
