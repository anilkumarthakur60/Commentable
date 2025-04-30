<?php

namespace Anil\Comments;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;

class WebCommentController extends CommentController
{
    public function __construct(
        public CommentService $commentService
    ) {
        parent::__construct();
    }

    /**
     * Creates a new comment for a given model.
     *
     * @throws Exception
     */
    public function store(Request $request): RedirectResponse
    {
        $comment = $this->commentService->store($request);
        /**
         * @var string $key
         */
        $key = $comment->getKey();
        $redirectUrl = URL::previous().'#comment-'.$key;

        return Redirect::to($redirectUrl);
    }

    /**
     * Updates the message of the comment.
     *
     * @throws Exception
     */
    public function update(Request $request, Comment $comment): RedirectResponse
    {
        $comment = $this->commentService->update($request, $comment);
        /**
         * @var string $key
         */
        $key = $comment->getKey();
        $redirectUrl = URL::previous().'#comment-'.$key;

        return Redirect::to($redirectUrl);
    }

    /**
     * Deletes a comment.
     *
     * @throws Exception
     */
    public function destroy(Comment $comment): RedirectResponse
    {
        $this->commentService->destroy($comment);

        return Redirect::back();
    }

    /**
     * Creates a reply "comment" to a comment.
     *
     * @throws Exception
     */
    public function reply(Request $request, Comment $comment): RedirectResponse
    {
        $reply = $this->commentService->reply($request, $comment);

        /**
         * @var string $key
         */
        $key = $reply->getKey();
        $redirectUrl = URL::previous().'#comment-'.$key;

        return Redirect::to($redirectUrl);
    }
}
