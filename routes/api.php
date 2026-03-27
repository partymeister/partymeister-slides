<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Motor\Core\Http\Middleware\V2\V2ErrorHandler;
use Partymeister\Slides\Events\PlaylistNextRequest;
use Partymeister\Slides\Events\PlaylistPreviousRequest;
use Partymeister\Slides\Events\SiegmeisterRequest;
use Partymeister\Slides\Http\Controllers\Api\FontsController;
use Partymeister\Slides\Http\Controllers\Api\PlaylistItemsController;
use Partymeister\Slides\Http\Controllers\Api\PlaylistsController;
use Partymeister\Slides\Http\Controllers\Api\ScreenshotCallbackController;
use Partymeister\Slides\Http\Controllers\Api\SlideClients\CommunicationController;
use Partymeister\Slides\Http\Controllers\Api\SlideClientsController;
use Partymeister\Slides\Http\Controllers\Api\SlidesController;
use Partymeister\Slides\Http\Controllers\Api\SlideTemplatesController;
use Partymeister\Slides\Http\Controllers\Api\TransitionsController;
use Partymeister\Slides\Http\Controllers\Api\V2;
use Partymeister\Slides\Services\XMLService;

// V2 API routes
Route::prefix('api/v2')
    ->name('v2.')
    ->middleware([V2ErrorHandler::class, 'auth:sanctum', 'bindings'])
    ->group(function () {
        Route::apiResource('transitions', V2\TransitionsController::class);
        Route::apiResource('slide-templates', V2\SlideTemplatesController::class);
        Route::apiResource('slides', V2\SlidesController::class);
        Route::apiResource('playlists', V2\PlaylistsController::class);
        Route::get('playlists/{playlist}/items', [V2\Playlists\ItemsController::class, 'index'])
            ->name('playlists.items.index');
    });

/**
 * Groups with token necessary
 */
Route::group([
    'middleware' => ['auth:api', 'bindings', 'permission'],
    'prefix' => 'api',
    'as' => 'api.',
], function () {
    Route::apiResource('slides', SlidesController::class);
    Route::apiResource('slide_templates', SlideTemplatesController::class);
    Route::apiResource('playlists', PlaylistsController::class);
    Route::apiResource('playlist_items', PlaylistItemsController::class);
    Route::apiResource('transitions', TransitionsController::class);
    Route::apiResource('slide_clients', SlideClientsController::class);
});

/**
 * Groups without token necessary
 */
Route::group([
    'middleware' => ['bindings'],
    'prefix' => 'api',
    'as' => 'api.',
], function () {
    Route::get('slidemeister/fonts', [FontsController::class, 'index']);
    Route::post('internal/screenshot-complete', [ScreenshotCallbackController::class, 'store']);
});

// FIXME: put this in a controller so we can use route caching
Route::post('ajax/slidemeister-web/{slide_client}/status', function (Request $request, $slide_client) {
    Cache::store('redis')
        ->put(config('cache.prefix').':slidemeister-web.'.$slide_client, $request->only([
            'cached_playlists',
            'current_playlist_id',
            'current_item_id',
        ]), 3600);
})
    ->name('ajax.slidemeister-web.status.update');

Route::group([
    'middleware' => ['web', 'web_auth', 'bindings', 'permission'],
    'prefix' => 'ajax',
    'as' => 'ajax.',
], function () {
    Route::get('transitions', [TransitionsController::class, 'index'])
        ->name('transitions.index');
    Route::get('playlists', [PlaylistsController::class, 'show'])
        ->name('playlists.index');
    Route::get('playlist_items/{playlist_item}', [PlaylistItemsController::class, 'show'])
        ->name('playlist_items.show');
    Route::post('slide_templates', [SlideTemplatesController::class, 'preview'])
        ->name('slide_templates.preview');
    Route::get('slides', [SlidesController::class, 'index'])
        ->name('slides.index');
    Route::post('slide_clients/communication/playlist', [CommunicationController::class, 'playlist'])
        ->name('slide_clients.communication.playlist');
    Route::post('slide_clients/communication/playnow', [CommunicationController::class, 'playnow'])
        ->name('slide_clients.communication.playnow');
    Route::post('slide_clients/communication/seek', [CommunicationController::class, 'seek'])
        ->name('slide_clients.communication.seek');
    Route::post('slide_clients/communication/seek_continue', [CommunicationController::class, 'seek_continue'])
        ->name('slide_clients.communication.seek_continue');
    Route::post('slide_clients/communication/siegmeister', [CommunicationController::class, 'siegmeister'])
        ->name('slide_clients.communication.siegmeister');
    Route::post('slide_clients/communication/skip', [CommunicationController::class, 'skip'])
        ->name('slide_clients.communication.skip');
    Route::get('slide_clients/communication/system', [CommunicationController::class, 'get_system_info'])
        ->name('slide_clients.communication.system');
    Route::get('slide_clients/communication/playlists', [CommunicationController::class, 'get_playlists'])
        ->name('slide_clients.communication.playlists');
});

// Route::group([
//    'middleware' => ['bindings'],
//    //'namespace'  => '',
//    'prefix' => 'ajax',
//    'as' => 'ajax.frontend.',
// ], function () {
//    Route::get('frontend-playlists/{playlist}', 'Partymeister\Slides\Http\Controllers\Api\PlaylistsController@show')->name('playlists.show');
// });

// H3 security audit: Disabled unauthenticated slide client control endpoints.
// Authenticated equivalents exist at /ajax/slide_clients/communication/skip and /siegmeister.
// Kept for reference — re-enable with proper auth if needed.
//
// Route::group([
//     'middleware' => ['bindings'],
//     'prefix'     => 'ajax',
//     'as'         => 'ajax.',
// ], function () {
//     Route::post('slide_clients/{slide_client}/communication/skip-for-revision', static function (
//         Request $request,
//         Partymeister\Slides\Models\SlideClient $client
//     ) {
//         session(['screens.active' => $client->id]);
//         if (is_null($client)) {
//             return response()->json(['message' => 'No slide client active'], 400);
//         }
//
//         switch ($client->type) {
//             case 'screens':
//                 $result = XMLService::send($request->get('direction'), ['hard' => $request->get('hard')]);
//                 if (! $result) {
//                     return response()->json(['result' => $result], 400);
//                 } else {
//                     return response()->json(['result' => $result]);
//                 }
//             // no break
//             case 'slidemeister-web':
//                 switch ($request->get('direction')) {
//                     case 'previous':
//                         event(new PlaylistPreviousRequest($request->get('hard', false)));
//                         break;
//                     case 'next':
//                         event(new PlaylistNextRequest($request->get('hard', false)));
//                         break;
//                 }
//
//                 return response()->json(['result' => 'Skip event sent']);
//                 break;
//         }
//     });
// });
//
// Route::group([
//     'middleware' => ['bindings'],
//     'prefix'     => 'ajax',
//     'as'         => 'ajax.',
// ], function () {
//     Route::post('slide_clients/{slide_client}/communication/prizegiving-for-revision', static function (
//         Request $request,
//         Partymeister\Slides\Models\SlideClient $client
//     ) {
//         session(['screens.active' => $client->id]);
//         if (is_null($client)) {
//             return response()->json(['message' => 'No slide client active'], 400);
//         }
//
//         switch ($client->type) {
//             case 'slidemeister-web':
//                 event(new SiegmeisterRequest());
//
//                 return response()->json(['result' => 'Siegmeister event sent']);
//                 break;
//         }
//     });
// });
