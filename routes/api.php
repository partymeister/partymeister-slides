<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Partymeister\Slides\Events\PlaylistNextRequest;
use Partymeister\Slides\Events\PlaylistPreviousRequest;
use Partymeister\Slides\Events\SiegmeisterRequest;
use Partymeister\Slides\Services\XMLService;

Route::group([
    'middleware' => ['auth:api', 'bindings', 'permission'],
    'namespace' => 'Partymeister\Slides\Http\Controllers\Api',
    'prefix' => 'api',
    'as' => 'api.',
], function () {
    Route::apiResource('slides', 'SlidesController');
    Route::apiResource('slide_templates', 'SlideTemplatesController');
    Route::apiResource('playlists', 'PlaylistsController');
    Route::apiResource('transitions', 'TransitionsController');
});

Route::group([
    'middleware' => ['bindings'],
    'namespace' => 'Partymeister\Slides\Http\Controllers\Api',
    'prefix' => 'api',
    'as' => 'api.',
], function () {
    Route::apiResource('slide_clients', 'SlideClientsController');
});

// FIXME: put this in a controller so we can use route caching
Route::post('ajax/slidemeister-web/{slide_client}/status', function (Request $request, $slide_client) {
    Cache::store('redis')->put(config('cache.prefix').':slidemeister-web.'.$slide_client, $request->all(), 3600);
})->name('ajax.slidemeister-web.status.update');

Route::group([
    'middleware' => ['web', 'web_auth', 'bindings', 'permission'],
    'namespace' => 'Partymeister\Slides\Http\Controllers\Api',
    'prefix' => 'ajax',
    'as' => 'ajax.',
], function () {
    Route::get('transitions', 'TransitionsController@index')->name('transitions.index');
    Route::get('playlists', 'PlaylistsController@show')->name('playlists.index');
    Route::get('playlists/items/{playlist_item}', 'Playlists\ItemsController@show')->name('playlists.items.show');
    Route::post('slide_templates', 'SlideTemplatesController@preview')->name('slide_templates.preview');
    Route::get('slides', 'SlidesController@index')->name('slides.index');
    Route::post('slide_clients/communication/playlist', 'SlideClients\CommunicationController@playlist')->name('slide_clients.communication.playlist');
    Route::post('slide_clients/communication/playnow', 'SlideClients\CommunicationController@playnow')->name('slide_clients.communication.playnow');
    Route::post('slide_clients/communication/seek', 'SlideClients\CommunicationController@seek')->name('slide_clients.communication.seek');
    Route::post('slide_clients/communication/siegmeister', 'SlideClients\CommunicationController@siegmeister')->name('slide_clients.communication.siegmeister');
    Route::post('slide_clients/communication/skip', 'SlideClients\CommunicationController@skip')->name('slide_clients.communication.skip');
    Route::get('slide_clients/communication/system', 'SlideClients\CommunicationController@get_system_info')->name('slide_clients.communication.system');
    Route::get('slide_clients/communication/playlists', 'SlideClients\CommunicationController@get_playlists')->name('slide_clients.communication.playlists');
});

Route::group([
    'middleware' => ['bindings'],
    //'namespace'  => '',
    'prefix' => 'ajax',
    'as' => 'ajax.frontend.',
], function () {
    //Route::post('slide_clients/communication/skip-for-revision', 'SlideClients\CommunicationController@skip')->name('slide_clients.communication.skipforrevision');
    Route::get('frontend-playlists/{playlist}', 'Partymeister\Slides\Http\Controllers\Api\PlaylistsController@show')->name('playlists.show');
});

Route::group([
    'middleware' => ['bindings'],
    'namespace' => 'Partymeister\Slides\Http\Controllers\Api',
    'prefix' => 'ajax',
    'as' => 'ajax.',
], function () {
    Route::post('slide_clients/{slide_client}/communication/skip-for-revision', static function (
        Request $request,
        \Partymeister\Slides\Models\SlideClient $client
    ) {

        session(['screens.active' => $client->id]);
        if (is_null($client)) {
            return response()->json(['message' => 'No slide client active'], 400);
        }

        switch ($client->type) {
            case 'screens':
                $result = XMLService::send($request->get('direction'), ['hard' => $request->get('hard')]);
                if (! $result) {
                    return response()->json(['result' => $result], 400);
                } else {
                    return response()->json(['result' => $result]);
                }
            // no break
            case 'slidemeister-web':
                switch ($request->get('direction')) {
                    case 'previous':
                        event(new PlaylistPreviousRequest($request->get('hard', false)));
                        break;
                    case 'next':
                        event(new PlaylistNextRequest($request->get('hard', false)));
                        break;
                }

                return response()->json(['result' => 'Skip event sent']);
                break;
        }
    });
});

Route::group([
    'middleware' => ['bindings'],
    'namespace' => 'Partymeister\Slides\Http\Controllers\Api',
    'prefix' => 'ajax',
    'as' => 'ajax.',
], function () {
    Route::post('slide_clients/{slide_client}/communication/prizegiving-for-revision', static function (
        Request $request,
        \Partymeister\Slides\Models\SlideClient $client
    ) {

        session(['screens.active' => $client->id]);
        if (is_null($client)) {
            return response()->json(['message' => 'No slide client active'], 400);
        }

        switch ($client->type) {
            case 'slidemeister-web':
                event(new SiegmeisterRequest());

                return response()->json(['result' => 'Siegmeister event sent']);
                break;
        }
    });
});
