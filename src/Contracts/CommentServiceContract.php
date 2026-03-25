<?php

namespace Anil\Comments\Contracts;

use Anil\Comments\Models\Comment;
use Illuminate\Http\Request;
use Throwable;

interface CommentServiceContract
{
    /**
     * Creates a new comment for a given model.
     *
     * @throws Throwable
     */
    public function store(Request $request): Comment;

    /**
     * Updates the message of an existing comment.
     *
     * @throws Throwable
     */
    public function update(Request $request, Comment $comment): Comment;

    /**
     * Deletes a comment (soft or hard based on config).
     *
     * @throws Throwable
     */
    public function destroy(Comment $comment): void;

    /**
     * Creates a reply to an existing comment.
     *
     * @throws Throwable
     */
    public function reply(Request $request, Comment $comment): Comment;
}
