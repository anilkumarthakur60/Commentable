<?php

namespace Anil\Comments\Contracts;

use Anil\Comments\Http\Requests\ReactRequest;
use Anil\Comments\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

interface CommentReactionContract
{
    /**
     * Toggles a reaction (like/dislike or any configured type) on a comment.
     */
    public function react(ReactRequest $request, Comment $comment): JsonResponse|RedirectResponse;
}
