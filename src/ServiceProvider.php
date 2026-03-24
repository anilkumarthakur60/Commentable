<?php

namespace Anil\Comments;

use Anil\Comments\Enums\UiTheme;
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
     * Register the @comments() Blade include directive for the active theme.
     */
    protected function includeBladeComponent(UiTheme $theme): void
    {
        Blade::include("comments::{$theme->value}.comments", 'comments');
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

    /**
     * Resolve the configured UI theme, falling back to Bootstrap 5.
     */
    protected function resolveTheme(): UiTheme
    {
        /** @var string $value */
        $value = Config::get('comments.ui_theme', UiTheme::Bootstrap5->value);

        return UiTheme::tryFrom($value) ?? UiTheme::Bootstrap5;
    }

    public function boot(): void
    {
        $theme = $this->resolveTheme();

        $this->loadRoutes();
        $this->loadMigrations();

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'comments');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'comments');

        $this->includeBladeComponent($theme);
        $this->definePermissions();

        // Share theme name with all views so they can reference sibling partials.
        view()->share('commentsTheme', $theme->value);

        // Bootstrap themes need the Bootstrap paginator styles.
        if ($theme->isBootstrap()) {
            Paginator::useBootstrap();
        }

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

        // Publish everything at once.
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
