<?php

namespace Partymeister\Slides\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Partymeister\Slides\Console\Commands\PartymeisterWebdriverCommand;
use Partymeister\Slides\Models\Component\ComponentPlaylistViewer;
use Partymeister\Slides\Models\Playlist;
use Partymeister\Slides\Models\PlaylistItem;
use Partymeister\Slides\Models\Slide;
use Partymeister\Slides\Models\SlideClient;
use Partymeister\Slides\Models\SlideTemplate;
use Partymeister\Slides\Models\Transition;

/**
 * Class PartymeisterServiceProvider
 */
class PartymeisterServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->config();
        $this->routes();
        $this->routeModelBindings();
        $this->translations();
        $this->views();
        $this->navigationItems();
        $this->permissions();
        $this->migrations();
        $this->publishResourceAssets();
        $this->registerCommands();
        $this->components();
        merge_local_config_with_db_configuration_variables('partymeister-slides');
    }

    public function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                PartymeisterWebdriverCommand::class,
            ]);
        }
    }

    public function config()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/partymeister-slides.php', 'partymeister-slides');
        $this->mergeConfigFrom(__DIR__.'/../../config/partymeister-slides-fonts.php', 'partymeister-slides-fonts');
    }

    public function routes()
    {
        if (! $this->app->routesAreCached()) {
            require __DIR__.'/../../routes/web.php';
            require __DIR__.'/../../routes/api.php';
        }
    }

    public function routeModelBindings()
    {
        Route::bind('slide', function ($id) {
            return Slide::findOrFail($id);
        });
        Route::bind('slide_template', function ($id) {
            return SlideTemplate::findOrFail($id);
        });
        Route::bind('playlist', function ($id) {
            return Playlist::findOrFail($id);
        });
        Route::bind('playlist_item', function ($id) {
            return PlaylistItem::findOrFail($id);
        });
        Route::bind('transition', function ($id) {
            return Transition::findOrFail($id);
        });
        Route::bind('slide_client', function ($id) {
            return SlideClient::findOrFail($id);
        });
        Route::bind('component_playlist_viewer', function ($id) {
            return ComponentPlaylistViewer::findOrFail($id);
        });
    }

    public function translations()
    {
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'partymeister-slides');

        $this->publishes([
            __DIR__.'/../../resources/lang' => resource_path('lang/vendor/partymeister-slides'),
        ], 'motor-backend-translations');
    }

    public function views()
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'partymeister-slides');

        $this->publishes([
            __DIR__.'/../../resources/views' => resource_path('views/vendor/partymeister-slides'),
        ], 'motor-backend-views');
    }

    public function navigationItems()
    {
        $config = $this->app['config']->get('motor-backend-navigation', []);
        $this->app['config']->set('motor-backend-navigation', array_replace_recursive(require __DIR__.'/../../config/motor-backend-navigation.php', $config));
    }

    public function permissions()
    {
        $config = $this->app['config']->get('motor-backend-permissions', []);
        $this->app['config']->set('motor-backend-permissions', array_replace_recursive(require __DIR__.'/../../config/motor-backend-permissions.php', $config));
    }

    public function migrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }

    public function publishResourceAssets()
    {
        $assets = [
            __DIR__.'/../../resources/assets/images' => resource_path('assets/images'),
            __DIR__.'/../../resources/assets/sass'   => resource_path('assets/sass'),
            __DIR__.'/../../resources/assets/npm'    => resource_path('assets/npm'),
            __DIR__.'/../../resources/assets/js'     => resource_path('assets/js'),
        ];

        $this->publishes($assets, 'partymeister-slides-install-resources');
    }

    public function components()
    {
        $config = $this->app['config']->get('motor-cms-page-components', []);
        $this->app['config']->set('motor-cms-page-components', array_replace_recursive(require __DIR__.'/../../config/motor-cms-page-components.php', $config));
    }
}
