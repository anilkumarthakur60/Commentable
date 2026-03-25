<?php

namespace Anil\Comments\Contracts;

use Anil\Comments\Http\Requests\ReplyCommentRequest;
use Anil\Comments\Http\Requests\StoreCommentRequest;
use Anil\Comments\Http\Requests\UpdateCommentRequest;
use Anil\Comments\Models\Comment;
use Throwable;

interface CommentServiceContract
{
    /**
     * Creates a new comment for a given model.
     *
     * @throws Throwable
     */
    public function store(StoreCommentRequest $request): Comment;

    /**
     * Updates the message of an existing comment.
     *
     * @throws Throwable
     */
    public function update(UpdateCommentRequest $request, Comment $comment): Comment;

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
    public function reply(ReplyCommentRequest $request, Comment $comment): Comment;
}
