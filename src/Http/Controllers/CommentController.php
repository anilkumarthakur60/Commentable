<?php

namespace Anil\Comments\Http\Controllers;

use Anil\Comments\Contracts\CommentCrudContract;
use Anil\Comments\Contracts\CommentReactionContract;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Spatie\Honeypot\ProtectAgainstSpam;

abstract class CommentController extends Controller implements CommentCrudContract, CommentReactionContract
{
    public function __construct()
    {
        /** @var list<string> $middleware */
        $middleware = Config::get('comments.middleware', ['web']);

        $this->middleware($middleware);

        if (Config::get('comments.guest_commenting')) {
            $this->middleware(ProtectAgainstSpam::class)->only('store');
        }

        if (Config::get('comments.rate_limiting.enabled')) {
            /** @var int $maxAttempts */
            $maxAttempts = Config::get('comments.rate_limiting.max_attempts', 10);
            /** @var int $decayMinutes */
            $decayMinutes = Config::get('comments.rate_limiting.decay_minutes', 1);

            $throttledActions = ['store', 'reply', 'update'];

            if (Config::get('comments.reactions.enabled', true)) {
                $throttledActions[] = 'react';
            }

            $this->middleware("throttle:{$maxAttempts},{$decayMinutes}")->only($throttledActions);
        }
    }
}
