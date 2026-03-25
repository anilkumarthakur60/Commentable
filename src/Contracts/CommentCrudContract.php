<?php

namespace Anil\Comments\Contracts;

use Anil\Comments\Http\Requests\ReplyCommentRequest;
use Anil\Comments\Http\Requests\StoreCommentRequest;
use Anil\Comments\Http\Requests\UpdateCommentRequest;
use Anil\Comments\Http\Resources\CommentResource;
use Anil\Comments\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

interface CommentCrudContract
{
    /**
     * Creates a new comment for a given model.
     *
     * @throws Throwable
     */
    public function store(StoreCommentRequest $request): RedirectResponse|CommentResource|JsonResponse;

    /**
     * Updates the message of the comment.
     *
     * @throws Throwable
     */
    public function update(UpdateCommentRequest $request, Comment $comment): RedirectResponse|CommentResource|JsonResponse;

    /**
     * Deletes a comment.
     *
     * @throws Throwable
     */
    public function destroy(Request $request, Comment $comment): RedirectResponse|JsonResponse;

    /**
     * Creates a reply to an existing comment.
     *
     * @throws Throwable
     */
    public function reply(ReplyCommentRequest $request, Comment $comment): RedirectResponse|CommentResource|JsonResponse;
}
