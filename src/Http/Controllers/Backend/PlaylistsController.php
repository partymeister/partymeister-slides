<?php

namespace Partymeister\Slides\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Kris\LaravelFormBuilder\FormBuilderTrait;
use League\Fractal\Manager;
use Motor\Backend\Http\Controllers\Controller;
use Motor\Media\Transformers\FileTransformer;
use Partymeister\Slides\Forms\Backend\PlaylistForm;
use Partymeister\Slides\Grids\PlaylistGrid;
use Partymeister\Slides\Http\Requests\Backend\PlaylistRequest;
use Partymeister\Slides\Http\Resources\PlaylistItemResource;
use Partymeister\Slides\Models\Playlist;
use Partymeister\Slides\Services\PlaylistService;
use Partymeister\Slides\Transformers\PlaylistItemTransformer;
use Partymeister\Slides\Transformers\SlideTransformer;

/**
 * Class PlaylistsController
 * @package Partymeister\Slides\Http\Controllers\Backend
 */
class PlaylistsController extends Controller
{
    use FormBuilderTrait;


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \ReflectionException
     */
    public function index()
    {
        $grid = new PlaylistGrid(Playlist::class);

        $service = PlaylistService::collection($grid);
        $grid->setFilter($service->getFilter());
        $paginator = $service->getPaginator();

        return view('partymeister-slides::backend.playlists.index', compact('paginator', 'grid'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $form = $this->form(PlaylistForm::class, [
            'method'  => 'POST',
            'route'   => 'backend.playlists.store',
            'enctype' => 'multipart/form-data'
        ]);

        $motorShowRightSidebar = true;
        $playlistItems         = [];

        return view(
            'partymeister-slides::backend.playlists.create',
            compact('form', 'motorShowRightSidebar', 'playlistItems')
        );
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param PlaylistRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(PlaylistRequest $request)
    {
        $form = $this->form(PlaylistForm::class);

        // It will automatically use current request, get the rules, and do the validation
        if (! $form->isValid()) {
            return redirect()->back()->withErrors($form->getErrors())->withInput();
        }

        PlaylistService::createWithForm($request, $form);

        flash()->success(trans('partymeister-slides::backend/playlists.created'));

        return redirect('backend/playlists');
    }


    /**
     * Display the specified resource.
     *
     * @param $id
     */
    public function show($id)
    {
        //
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param Playlist $record
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Request $request, Playlist $record)
    {
        $form = $this->form(PlaylistForm::class, [
            'method'  => 'PATCH',
            'url'     => route('backend.playlists.update', [ $record->id ]),
            'enctype' => 'multipart/form-data',
            'model'   => $record
        ]);

        $motorShowRightSidebar = true;

        $this->fractal = new Manager();

        $playlistItemCollection = PlaylistItemResource::collection($record->items);
        $playlistItemResponse = $playlistItemCollection->toResponse($request);
        $playlistItems = Arr::get(json_decode($playlistItemResponse->getContent(), true), 'data');
        //
        //foreach ($record->items as $item) {
        //    $playlistItems = EntryResource::collection($competition->qualified_entries->load('competition'));
        //
        //
        //
        //
        //    $f = null;
        //    $i = new PlaylistItemResource($item);
        //    dd($i->toArray($request));
        //    //$i = fractal($item, new PlaylistItemTransformer())->toArray();
        //    if ($item->slide_id != null) {
        //        $f = fractal($item->slide, new SlideTransformer())->toArray();
        //    } elseif ($item->file_association != null) {
        //        $f = fractal($item->file_association->file, new FileTransformer())->toArray();
        //    }
        //
        //    if ($f != null) {
        //        $i['data'] = array_merge($i['data'], $f['data']);
        //        //$i['data']['file'] = $f['data'];
        //        $playlistItems[] = $i['data'];
        //    }
        //}

        $playlistItems = json_encode($playlistItems, JSON_UNESCAPED_SLASHES);

        return view(
            'partymeister-slides::backend.playlists.edit',
            compact('form', 'motorShowRightSidebar', 'playlistItems')
        );
    }


    /**
     * Update the specified resource in storage.
     *
     * @param PlaylistRequest $request
     * @param Playlist        $record
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(PlaylistRequest $request, Playlist $record)
    {
        $form = $this->form(PlaylistForm::class);

        // It will automatically use current request, get the rules, and do the validation
        if (! $form->isValid()) {
            return redirect()->back()->withErrors($form->getErrors())->withInput();
        }

        PlaylistService::updateWithForm($record, $request, $form);

        flash()->success(trans('partymeister-slides::backend/playlists.updated'));

        return redirect('backend/playlists');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param Playlist $record
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy(Playlist $record)
    {
        PlaylistService::delete($record);

        flash()->success(trans('partymeister-slides::backend/playlists.deleted'));

        return redirect('backend/playlists');
    }
}
