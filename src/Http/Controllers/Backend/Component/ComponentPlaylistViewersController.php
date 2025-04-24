<?php

namespace Partymeister\Slides\Http\Controllers\Backend\Component;

use Illuminate\Http\Request;
use Kris\LaravelFormBuilder\FormBuilderTrait;
use Motor\CMS\Http\Controllers\Component\ComponentController;
use Partymeister\Slides\Forms\Backend\Component\ComponentPlaylistViewerForm;
use Partymeister\Slides\Models\Component\ComponentPlaylistViewer;
use Partymeister\Slides\Services\Component\ComponentPlaylistViewerService;

class ComponentPlaylistViewersController extends ComponentController
{
    use FormBuilderTrait;

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->form = $this->form(ComponentPlaylistViewerForm::class);

        return response()->json($this->getFormData('component.playlist-viewers.store', ['mediapool' => false]));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->form = $this->form(ComponentPlaylistViewerForm::class);

        if (! $this->isValid()) {
            return $this->respondWithValidationError();
        }

        ComponentPlaylistViewerService::createWithForm($request, $this->form);

        return response()->json(['message' => trans('partymeister-slides::component/playlist-viewers.created')]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(ComponentPlaylistViewer $record)
    {
        $this->form = $this->form(ComponentPlaylistViewerForm::class, [
            'model' => $record,
        ]);

        return response()->json($this->getFormData('component.playlist-viewers.update', ['mediapool' => false]));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ComponentPlaylistViewer $record)
    {
        $form = $this->form(ComponentPlaylistViewerForm::class);

        if (! $this->isValid()) {
            return $this->respondWithValidationError();
        }

        ComponentPlaylistViewerService::updateWithForm($record, $request, $form);

        return response()->json(['message' => trans('partymeister-slides::component/playlist-viewers.updated')]);
    }
}
