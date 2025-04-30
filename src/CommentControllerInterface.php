<?php

namespace Anil\Comments;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

interface CommentControllerInterface
{
    /**
     * Creates a new comment for a given model.
     *
     * @throws Exception
     */
    public function store(Request $request): RedirectResponse|CommentResource|JsonResponse;

    /**
     * Updates the message of the comment.
     *
     * @throws Exception
     */
    public function update(Request $request, Comment $comment): RedirectResponse|CommentResource|JsonResponse;

    /**
     * Deletes a comment.
     *
     * @throws Exception
     */
    public function destroy(Request $request, Comment $comment): RedirectResponse|JsonResponse;

    /**
     * Creates a reply "comment" to a comment.
     *
     * @throws Exception
     */
    public function reply(Request $request, Comment $comment): RedirectResponse|CommentResource|JsonResponse;
}
