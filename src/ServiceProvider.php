<?php

namespace Anil\Comments;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * If routes are enabled (by default they are),
     * then load the routes, otherwise don't load
     * the routes.
     */
    protected function loadRoutes(): void
    {
        if (Config::get('comments.routes') === true) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }
    }

    /**
     * If load_migrations config is true (by default it is),
     * then load the package migrations, otherwise don't load
     * the migrations.
     */
    protected function loadMigrations(): void
    {
        if (Config::get('comments.load_migrations') === true) {
            $this->loadMigrationsFrom(__DIR__.'/../migrations');
        }
    }

    /**
     * If for some reason you want to override the component.
     */
    protected function includeBladeComponent(): void
    {
        Blade::include('comments::components.comments', 'comments');
    }

    /**
     * Define permission defined in the config.
     */
    protected function definePermissions(): void
    {
        /**
         * @var array<string, string> $permissions
         */
        $permissions = Config::get('comments.permissions', []);
        foreach ($permissions as $permission => $policy) {
            Gate::define($permission, $policy);
        }
    }

    public function boot(): void
    {
        $this->loadRoutes();

        $this->loadMigrations();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'comments');

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'comments');

        $this->includeBladeComponent();

        $this->definePermissions();

        $this->publishes([
            __DIR__.'/../migrations/' => App::databasePath('migrations'),
        ], 'migrations');

        $this->publishes([
            __DIR__.'/../resources/views' => App::resourcePath('views/vendor/comments'),
        ], 'views');

        $this->publishes([
            __DIR__.'/../config/comments.php' => App::configPath('comments.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../resources/lang' => App::resourcePath('lang/vendor/comments'),
        ], 'translations');

        /**
         * @var string $model
         */
        $model = Config::get('comments.model');
        Route::model($model, $model);

        if (Config::get('comments.paginator_use_bootstrap', true)) {
            Paginator::useBootstrap();
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/comments.php',
            'comments'
        );
    }
}
