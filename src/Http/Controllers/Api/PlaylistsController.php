<?php

namespace Partymeister\Slides\Http\Controllers\Api;

use Motor\Backend\Http\Controllers\Controller;

use Partymeister\Slides\Models\Playlist;
use Partymeister\Slides\Http\Requests\Backend\PlaylistRequest;
use Partymeister\Slides\Services\PlaylistService;
use Partymeister\Slides\Transformers\PlaylistTransformer;

class PlaylistsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $paginator = PlaylistService::collection()->getPaginator();
        $resource = $this->transformPaginator($paginator, PlaylistTransformer::class);

        return $this->respondWithJson('Playlist collection read', $resource);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(PlaylistRequest $request)
    {
        $result = PlaylistService::create($request)->getResult();
        $resource = $this->transformItem($result, PlaylistTransformer::class);

        return $this->respondWithJson('Playlist created', $resource);
    }


    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Playlist $record)
    {
        $result = PlaylistService::show($record)->getResult();
        $resource = $this->transformItem($result, PlaylistTransformer::class);

        return $this->respondWithJson('Playlist read', $resource);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(PlaylistRequest $request, Playlist $record)
    {
        $result = PlaylistService::update($record, $request)->getResult();
        $resource = $this->transformItem($result, PlaylistTransformer::class);

        return $this->respondWithJson('Playlist updated', $resource);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Playlist $record)
    {
        $result = PlaylistService::delete($record)->getResult();

        if ($result) {
            return $this->respondWithJson('Playlist deleted', ['success' => true]);
        }
        return $this->respondWithJson('Playlist NOT deleted', ['success' => false]);
    }
}