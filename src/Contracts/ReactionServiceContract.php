<?php

namespace Anil\Comments\Contracts;

use Anil\Comments\Models\Comment;
use Illuminate\Http\Request;

interface ReactionServiceContract
{
    /**
     * Toggles a reaction on a comment for the authenticated user.
     *
     * @return array{reactions: array<string, int>, user_reaction: string|null}
     */
    public function react(Request $request, Comment $comment): array;
}
