<?php

namespace Anil\Comments;

use Anil\Comments\Contracts\CommentServiceContract;
use Anil\Comments\Contracts\ReactionServiceContract;
use Anil\Comments\Models\Comment;
use Anil\Comments\Services\CommentService;
use Anil\Comments\Services\ReactionService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/comments.php',
            'comments'
        );

        $this->app->bind(CommentServiceContract::class, CommentService::class);
        $this->app->bind(ReactionServiceContract::class, ReactionService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutes();
        $this->loadMigrations();
        $this->loadViews();
        $this->loadTranslations();
        $this->definePermissions();
        $this->bindRouteModel();
        $this->registerPublishables();
    }

    /**
     * Load package routes when enabled.
     */
    protected function loadRoutes(): void
    {
        if (Config::get('comments.routes') === true) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }
    }

    /**
     * Load package migrations when enabled.
     */
    protected function loadMigrations(): void
    {
        if (Config::get('comments.load_migrations') === true) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }

    /**
     * Load package views and register the @comments Blade directive.
     */
    protected function loadViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'comments');

        // Single framework-agnostic theme (pure CSS, no Bootstrap/Tailwind required).
        Blade::include('comments::comments.comments', 'comments');
    }

    /**
     * Load package translations.
     */
    protected function loadTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'comments');
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
     * Bind the {comment} route model parameter to the configured Comment model.
     */
    protected function bindRouteModel(): void
    {
        /** @var class-string<Comment> $model */
        $model = Config::get('comments.model');
        Route::model('comment', $model);
    }

    /**
     * Register all publishable assets.
     */
    protected function registerPublishables(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../database/migrations' => App::databasePath('migrations'),
        ], 'comments-migrations');

        $this->publishes([
            __DIR__.'/../resources/views' => App::resourcePath('views/vendor/comments'),
        ], 'comments-views');

        $this->publishes([
            __DIR__.'/../config/comments.php' => App::configPath('comments.php'),
        ], 'comments-config');

        $this->publishes([
            __DIR__.'/../resources/lang' => App::resourcePath('lang/vendor/comments'),
        ], 'comments-translations');

        // Publish everything at once with the 'comments' tag.
        $this->publishes([
            __DIR__.'/../database/migrations' => App::databasePath('migrations'),
            __DIR__.'/../resources/views'     => App::resourcePath('views/vendor/comments'),
            __DIR__.'/../config/comments.php' => App::configPath('comments.php'),
            __DIR__.'/../resources/lang'      => App::resourcePath('lang/vendor/comments'),
        ], 'comments');
    }
}
