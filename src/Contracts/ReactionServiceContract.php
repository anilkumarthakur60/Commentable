<?php

namespace Anil\Comments\Contracts;

use Anil\Comments\Http\Requests\ReactRequest;
use Anil\Comments\Models\Comment;

interface ReactionServiceContract
{
    /**
     * Toggles a reaction on a comment for the authenticated user.
     *
     * @return array{reactions: array<string, int>, user_reaction: string|null}
     */
    public function react(ReactRequest $request, Comment $comment): array;
}
