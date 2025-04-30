<?php

namespace Anil\Comments;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

interface CommentControllerInterface
{
    /**
     * Creates a new comment for a given model.
     *
     * @throws Exception
     */
    public function store(Request $request): RedirectResponse;

    /**
     * Updates the message of the comment.
     *
     * @throws Exception
     */
    public function update(Request $request, Comment $comment): RedirectResponse;

    /**
     * Deletes a comment.
     *
     * @throws Exception
     */
    public function destroy(Comment $comment): RedirectResponse;

    /**
     * Creates a reply "comment" to a comment.
     *
     * @throws Exception
     */
    public function reply(Request $request, Comment $comment): RedirectResponse;
}
