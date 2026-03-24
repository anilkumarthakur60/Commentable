<?php

namespace Anil\Comments;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Load package routes when enabled.
     */
    protected function loadRoutes(): void
    {
        if (Config::get('comments.routes') === true) {
            $this->loadRoutesFrom(__DIR__ . '/routes.php');
        }
    }

    /**
     * Load package migrations when enabled.
     */
    protected function loadMigrations(): void
    {
        if (Config::get('comments.load_migrations') === true) {
            $this->loadMigrationsFrom(__DIR__ . '/../migrations');
        }
    }

    /**
     * Register the Gate permissions defined in the config.
     */
    protected function definePermissions(): void
    {
        /** @var array<string, array{class-string, string}> $permissions */
        $permissions = Config::get('comments.permissions', []);

        foreach ($permissions as $ability => $policy) {
            Gate::define($ability, $policy);
        }
    }

    public function boot(): void
    {
        $this->loadRoutes();
        $this->loadMigrations();

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'comments');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'comments');

        // Single framework-agnostic theme (pure CSS, no Bootstrap/Tailwind required).
        Blade::include('comments::comments.comments', 'comments');

        $this->definePermissions();

        /** @var class-string<Comment> $model */
        $model = Config::get('comments.model');
        Route::model('comment', $model);

        $this->publishes([
            __DIR__ . '/../migrations/' => App::databasePath('migrations'),
        ], 'comments-migrations');

        $this->publishes([
            __DIR__ . '/../resources/views' => App::resourcePath('views/vendor/comments'),
        ], 'comments-views');

        $this->publishes([
            __DIR__ . '/../config/comments.php' => App::configPath('comments.php'),
        ], 'comments-config');

        $this->publishes([
            __DIR__ . '/../resources/lang' => App::resourcePath('lang/vendor/comments'),
        ], 'comments-translations');

        $this->publishes([
            __DIR__ . '/../migrations/'         => App::databasePath('migrations'),
            __DIR__ . '/../resources/views'     => App::resourcePath('views/vendor/comments'),
            __DIR__ . '/../config/comments.php' => App::configPath('comments.php'),
            __DIR__ . '/../resources/lang'      => App::resourcePath('lang/vendor/comments'),
        ], 'comments');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/comments.php',
            'comments'
        );
    }
}
