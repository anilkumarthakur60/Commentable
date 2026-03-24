<?php

namespace Anil\Comments;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

interface CommentControllerInterface
{
    /**
     * Creates a new comment for a given model.
     *
     * @throws Throwable
     */
    public function store(Request $request): RedirectResponse|CommentResource|JsonResponse;

    /**
     * Updates the message of the comment.
     *
     * @throws Throwable
     */
    public function update(Request $request, Comment $comment): RedirectResponse|CommentResource|JsonResponse;

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
    public function reply(Request $request, Comment $comment): RedirectResponse|CommentResource|JsonResponse;

    /**
     * Toggles a reaction (like/dislike or any configured type) on a comment.
     */
    public function react(Request $request, Comment $comment): JsonResponse|RedirectResponse;
}
