<?php

use Partymeister\Slides\Http\Controllers\SlidemeisterWebController;
use Partymeister\Slides\Http\Controllers\Backend\Component\ComponentPlaylistViewersController;
use Partymeister\Slides\Http\Controllers\Backend\SlidesController;
use Partymeister\Slides\Http\Controllers\Backend\SlideTemplatesController;
use Partymeister\Slides\Http\Controllers\Backend\PlaylistsController;
use Partymeister\Slides\Http\Controllers\Backend\TransitionsController;
use Partymeister\Slides\Http\Controllers\Backend\SlideClientsController;
use Partymeister\Slides\Http\Controllers\Backend\FilesController;

Route::get('slidemeister-web/{slide_client}', [SlidemeisterWebController::class, 'index'])
     ->middleware(['bindings'])
     ->name('backend.slidemeister-web.show');

// FIXME: This route still embeds the admin user's API token in the page for the Vue app
// to make authenticated API calls. Ideally, the headless generator should use a short-lived
// scoped token instead of the admin's permanent token. The shared secret below prevents
// unauthorized access to the route, but the admin token is still exposed to valid callers.
Route::get('internal/generate/schedule/{schedule}', function (\Illuminate\Http\Request $request, \Partymeister\Core\Models\Schedule $schedule) {
    $secret = config('partymeister-slides.screenshot_secret');
    if (empty($secret) || ! hash_equals($secret, (string) $request->query('secret', ''))) {
        abort(403, 'Invalid screenshot secret');
    }

    return view('partymeister-slides::slidemeister-generator.index', [
        'generator_type' => 'timetable',
        'schedule_id' => $schedule->id,
        'api_token' => \Motor\Admin\Models\User::find(1)?->api_token ?? abort(500, 'Admin user not found'),
        'headless' => true,
        'base_url' => config('app.url_internal', config('app.url')),
    ]);
})->middleware(['web', 'bindings'])->name('internal.generate.schedule');

Route::group(['middleware' => ['web', 'web_auth', 'bindings']], function () {
    Route::get('slidemeister-editor', function () {
        return view('partymeister-slides::slidemeister-editor.index', [
            'editor_mode' => 'start',
            'api_token' => auth()->user()->api_token,
        ]);
    })->name('backend.slidemeister-editor.start');

    Route::get('slidemeister-editor/template/{slide_template}', function (\Partymeister\Slides\Models\SlideTemplate $slideTemplate) {
        return view('partymeister-slides::slidemeister-editor.index', [
            'editor_mode' => 'template',
            'entity_id' => $slideTemplate->id,
            'api_token' => auth()->user()->api_token,
        ]);
    })->name('backend.slidemeister-editor.template');

    Route::get('slidemeister-editor/slide/{slide}', function (\Partymeister\Slides\Models\Slide $slide) {
        return view('partymeister-slides::slidemeister-editor.index', [
            'editor_mode' => 'slide',
            'entity_id' => $slide->id,
            'api_token' => auth()->user()->api_token,
        ]);
    })->name('backend.slidemeister-editor.slide');

    Route::get('slidemeister-generator', function () {
        return view('partymeister-slides::slidemeister-generator.index', [
            'generator_type' => 'start',
            'api_token' => auth()->user()->api_token,
        ]);
    })->name('backend.slidemeister-generator.start');

    Route::get('slidemeister-generator/competition/{competition}', function (\Partymeister\Competitions\Models\Competition $competition) {
        return view('partymeister-slides::slidemeister-generator.index', [
            'generator_type' => 'competition',
            'competition_id' => $competition->id,
            'api_token' => auth()->user()->api_token,
        ]);
    })->name('backend.slidemeister-generator.competition');

    Route::get('slidemeister-generator/schedule/{schedule}', function (\Partymeister\Core\Models\Schedule $schedule) {
        return view('partymeister-slides::slidemeister-generator.index', [
            'generator_type' => 'timetable',
            'schedule_id' => $schedule->id,
            'api_token' => auth()->user()->api_token,
        ]);
    })->name('backend.slidemeister-generator.schedule');

    Route::get('slidemeister-generator/event/{event}', function (\Partymeister\Core\Models\Event $event) {
        return view('partymeister-slides::slidemeister-generator.index', [
            'generator_type' => 'event',
            'event_id' => $event->id,
            'api_token' => auth()->user()->api_token,
        ]);
    })->name('backend.slidemeister-generator.event');

    Route::get('slidemeister-generator/prizegiving', function () {
        return view('partymeister-slides::slidemeister-generator.index', [
            'generator_type' => 'prizegiving',
            'api_token' => auth()->user()->api_token,
        ]);
    })->name('backend.slidemeister-generator.prizegiving');
});

Route::group([
    'as'         => 'component.',
    'prefix'     => 'component',
    'middleware' => [
        'web',
    ],
], function () {
    // You only need this part if you already have a component group for the given namespace
    Route::get('playlist-viewers', [ComponentPlaylistViewersController::class, 'create'])
         ->name('playlist-viewers.create');
    Route::post('playlist-viewers', [ComponentPlaylistViewersController::class, 'store'])
         ->name('playlist-viewers.store');
    Route::get('playlist-viewers/{component_playlist_viewer}', [ComponentPlaylistViewersController::class, 'edit'])
         ->name('playlist-viewers.edit');
    Route::patch('playlist-viewers/{component_playlist_viewer}', [ComponentPlaylistViewersController::class, 'update'])
         ->name('playlist-viewers.update');
});

Route::group([
    'as'         => 'backend.',
    'prefix'     => 'backend',
    'middleware' => [
        'web',
        'web_auth',
        'navigation',
    ],
], function () {
    Route::group(['middleware' => ['permission']], function () {
        Route::resource('slides', SlidesController::class)
             ->except(['create', 'show']);
        Route::get('slides/{slide}/duplicate', [SlidesController::class, 'duplicate'])
             ->name('slides.duplicate');

        Route::get('slides/create/{slide_template}', [SlidesController::class, 'create'])
             ->name('slides.create');

        Route::resource('slide_templates', SlideTemplatesController::class)
             ->except('show');
        Route::get('slide_templates/{slide_template}/duplicate', [SlideTemplatesController::class, 'duplicate'])
             ->name('slide_templates.duplicate');

        Route::resource('playlists', PlaylistsController::class);
        Route::resource('transitions', TransitionsController::class);
        Route::resource('slide_clients', SlideClientsController::class);
        Route::get('slide_clients/{slide_client}/activate', [SlideClientsController::class, 'activate'])
             ->name('slide_clients.activate');

        Route::resource('files', FilesController::class);
    });
});

Route::get('backend/slide_templates/{slide_template}.html', [SlideTemplatesController::class, 'show'])
     ->middleware(['bindings'])
     ->name('backend.slide_templates.show');
Route::get('backend/slides/{slide}.html', [SlidesController::class, 'show'])
     ->middleware([
         'bindings',
         'cache.headers:etag',
     ])
     ->name('backend.slides.show');
Route::get('backend/slides/{slide}/render', [SlidesController::class, 'render'])
     ->middleware(['bindings'])
     ->name('backend.slides.render');
Route::get('backend/slides/render-preview/{cacheKey}', [SlidesController::class, 'renderPreview'])
     ->name('backend.slides.render-preview');

// FIXME: put these in controllers so we can use the Route caching
//Route::get('test-prizegiving', function() {
//    $xml = \Partymeister\Slides\Services\XMLService::send('playlist', array('playlist_id' => 148));
//    return response($xml, 200)
//        ->header('Content-Type', 'text/xml');    //echo $xml;
//});
//
//Route::get('xmlservice/playlist', function() {
//    //$result = XMLMeister::send('playlist', array('playlist_id' => arr::get($_GET, 'play'), 'callbacks' => arr::get($_GET, 'callbacks')));
//    $xml = \Partymeister\Slides\Services\XMLService::send('playlist', ['playlist_id' => \Partymeister\Slides\Models\Playlist::find(194)->id, 'callbacks' => 0], false, true);
//    return response($xml, 200)
//        ->header('Content-Type', 'text/xml');    //echo $xml;
//});
//
//Route::get('xmlservice/next', function() {
//    $xml = \Partymeister\Slides\Services\XMLService::send('next', ['hard' => true], false, true);
//    return response($xml, 200)
//        ->header('Content-Type', 'text/xml');    //echo $xml;
//});
//
//Route::get('xmlservice/previous', function() {
//    $xml = \Partymeister\Slides\Services\XMLService::send('previous', ['hard' => true], false, true);
//    return response($xml, 200)
//        ->header('Content-Type', 'text/xml');    //echo $xml;
//});
//
//Route::get('xmlservice/getplaylists', function() {
//    $xml = \Partymeister\Slides\Services\XMLService::send('get_playlists', ['hard' => true], false, true);
//    return response($xml, 200)
//        ->header('Content-Type', 'text/xml');    //echo $xml;
//});
