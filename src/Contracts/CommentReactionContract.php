<?php

namespace Anil\Comments\Contracts;

use Anil\Comments\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

interface CommentReactionContract
{
    /**
     * Toggles a reaction (like/dislike or any configured type) on a comment.
     */
    public function react(Request $request, Comment $comment): JsonResponse|RedirectResponse;
}
