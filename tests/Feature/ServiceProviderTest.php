<?php

use Anil\Comments\Contracts\CommentServiceContract;
use Anil\Comments\Contracts\ReactionServiceContract;
use Anil\Comments\ServiceProvider;
use Anil\Comments\Services\CommentService;
use Anil\Comments\Services\ReactionService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

describe('ServiceProvider', function () {
    it('registers CommentServiceContract binding', function () {
        $service = app(CommentServiceContract::class);
        expect($service)->toBeInstanceOf(CommentService::class);
    });

    it('registers ReactionServiceContract binding', function () {
        $service = app(ReactionServiceContract::class);
        expect($service)->toBeInstanceOf(ReactionService::class);
    });

    it('registers comment routes when routes config is true', function () {
        expect(Route::has('comments.store'))->toBeTrue()
            ->and(Route::has('comments.update'))->toBeTrue()
            ->and(Route::has('comments.destroy'))->toBeTrue()
            ->and(Route::has('comments.reply'))->toBeTrue();
    });

    it('registers react route when reactions are enabled', function () {
        Config::set('comments.reactions.enabled', true);
        expect(Route::has('comments.react'))->toBeTrue();
    });

    it('defines gate permissions from config', function () {
        expect(Gate::has('create-comment'))->toBeTrue()
            ->and(Gate::has('edit-comment'))->toBeTrue()
            ->and(Gate::has('delete-comment'))->toBeTrue()
            ->and(Gate::has('reply-to-comment'))->toBeTrue();
    });

    it('merges config correctly', function () {
        expect(Config::get('comments.model'))->not->toBeNull()
            ->and(Config::get('comments.reactions.enabled'))->toBeBool()
            ->and(Config::get('comments.max_depth'))->toBeInt()
            ->and(Config::get('comments.route_prefix'))->toBeString();
    });

    it('provides publishable tags', function () {
        $publishableProviders = app()->make('Illuminate\Foundation\Application')
            ->getProviders(ServiceProvider::class);

        expect($publishableProviders)->not->toBeEmpty();
    });
});
