<?php

use Motor\Core\Http\Middleware\V2\V2ErrorHandler;
use Partymeister\Slides\Http\Controllers\Api\V2;

// V2 public API routes (no auth)
Route::prefix('api/v2')
    ->name('v2.public.')
    ->middleware([V2ErrorHandler::class, 'bindings'])
    ->group(function () {
        Route::get('fonts', [V2\FontsController::class, 'index'])->name('fonts.index');
    });

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
        Route::apiResource('slide-clients', V2\SlideClientsController::class);
    });

// V2 RPC routes
Route::prefix('api/v2/rpc')
    ->name('v2.rpc.')
    ->middleware([V2ErrorHandler::class, 'auth:sanctum', 'bindings'])
    ->group(function () {
        Route::post('slide-clients/playlist', V2\Rpc\SlideClients\PlaylistController::class)
            ->name('slide-clients.playlist');
        Route::post('slide-clients/playnow', V2\Rpc\SlideClients\PlayNowController::class)
            ->name('slide-clients.playnow');
        Route::post('slide-clients/seek', V2\Rpc\SlideClients\SeekController::class)
            ->name('slide-clients.seek');
        Route::post('slide-clients/skip', V2\Rpc\SlideClients\SkipController::class)
            ->name('slide-clients.skip');
        Route::post('slide-clients/siegmeister', V2\Rpc\SlideClients\SiegmeisterController::class)
            ->name('slide-clients.siegmeister');
        Route::post('slides/screenshot-complete', V2\Rpc\Slides\ScreenshotCompleteController::class)
            ->name('slides.screenshot-complete');
    });
