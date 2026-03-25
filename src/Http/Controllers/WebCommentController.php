<?php

namespace Anil\Comments\Http\Controllers;

use Anil\Comments\Contracts\CommentServiceContract;
use Anil\Comments\Contracts\ReactionServiceContract;
use Anil\Comments\Http\Requests\ReactRequest;
use Anil\Comments\Http\Requests\ReplyCommentRequest;
use Anil\Comments\Http\Requests\StoreCommentRequest;
use Anil\Comments\Http\Requests\UpdateCommentRequest;
use Anil\Comments\Http\Resources\CommentResource;
use Anil\Comments\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Throwable;

class WebCommentController extends CommentController
{
    public function __construct(
        public readonly CommentServiceContract $commentService,
        public readonly ReactionServiceContract $reactionService,
    ) {
        parent::__construct();
    }

    /**
     * Creates a new comment for a given model.
     *
     * @throws Throwable
     */
    public function store(StoreCommentRequest $request): RedirectResponse|CommentResource|JsonResponse
    {
        $comment = $this->commentService->store($request);

        if ($request->wantsJson()) {
            $status = Config::get('comments.response_status.created', 201);

            return CommentResource::make($comment)
                ->response()
                ->setStatusCode(is_int($status) ? $status : 201);
        }

        return Redirect::to(URL::previous().'#comment-'.$comment->id)
            ->with('success', Config::get('comments.response_messages.created'));
    }

    /**
     * Updates the message of the comment.
     *
     * @throws Throwable
     */
    public function update(UpdateCommentRequest $request, Comment $comment): RedirectResponse|CommentResource|JsonResponse
    {
        $comment = $this->commentService->update($request, $comment);

        if ($request->wantsJson()) {
            $status = Config::get('comments.response_status.updated', 200);

            return CommentResource::make($comment)
                ->response()
                ->setStatusCode(is_int($status) ? $status : 200);
        }

        return Redirect::to(URL::previous().'#comment-'.$comment->id)
            ->with('success', Config::get('comments.response_messages.updated'));
    }

    /**
     * Deletes a comment.
     *
     * @throws Throwable
     */
    public function destroy(Request $request, Comment $comment): RedirectResponse|JsonResponse
    {
        $this->commentService->destroy($comment);

        if ($request->wantsJson()) {
            $status = Config::get('comments.response_status.deleted', 200);

            return response()->json(
                ['message' => Config::get('comments.response_messages.deleted')],
                is_int($status) ? $status : 200
            );
        }

        return Redirect::back()
            ->with('success', Config::get('comments.response_messages.deleted'));
    }

    /**
     * Toggles a reaction on a comment.
     *
     * Returns 404 when reactions are disabled in config.
     */
    public function react(ReactRequest $request, Comment $comment): JsonResponse|RedirectResponse
    {
        if (! Config::get('comments.reactions.enabled', true)) {
            abort(404);
        }

        $result = $this->reactionService->react($request, $comment);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return Redirect::to(URL::previous().'#comment-'.$comment->id);
    }

    /**
     * Creates a reply to an existing comment.
     *
     * @throws Throwable
     */
    public function reply(ReplyCommentRequest $request, Comment $comment): RedirectResponse|CommentResource|JsonResponse
    {
        $reply = $this->commentService->reply($request, $comment);

        if ($request->wantsJson()) {
            $status = Config::get('comments.response_status.created', 201);

            return CommentResource::make($reply)
                ->response()
                ->setStatusCode(is_int($status) ? $status : 201);
        }

        return Redirect::to(URL::previous().'#comment-'.$reply->id)
            ->with('success', Config::get('comments.response_messages.created'));
    }
}
