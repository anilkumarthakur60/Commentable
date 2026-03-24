<?php

namespace Anil\Comments;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Throwable;

class WebCommentController extends CommentController implements CommentControllerInterface
{
    public function __construct(
        public readonly CommentService $commentService
    ) {
        parent::__construct();
    }

    /**
     * Creates a new comment for a given model.
     *
     * @throws Throwable
     */
    public function store(Request $request): RedirectResponse|CommentResource|JsonResponse
    {
        $comment = $this->commentService->store($request);

        if ($request->wantsJson()) {
            return CommentResource::make($comment)
                ->response()
                ->setStatusCode(Config::get('comments.response_status.created', 201));
        }

        return Redirect::to(URL::previous().'#comment-'.$comment->getKey())
            ->with('success', Config::get('comments.response_messages.created'));
    }

    /**
     * Updates the message of the comment.
     *
     * @throws Throwable
     */
    public function update(Request $request, Comment $comment): RedirectResponse|CommentResource|JsonResponse
    {
        $comment = $this->commentService->update($request, $comment);

        if ($request->wantsJson()) {
            return CommentResource::make($comment)
                ->response()
                ->setStatusCode(Config::get('comments.response_status.updated', 200));
        }

        return Redirect::to(URL::previous().'#comment-'.$comment->getKey())
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
            return response()->json(
                ['message' => Config::get('comments.response_messages.deleted')],
                Config::get('comments.response_status.deleted', 200)
            );
        }

        return Redirect::back()
            ->with('success', Config::get('comments.response_messages.deleted'));
    }

    /**
     * Toggles a reaction on a comment. Delegates all business logic to the service.
     * Returns 404 when reactions are disabled in config.
     * Returns 401 when the user is not authenticated.
     */
    public function react(Request $request, Comment $comment): JsonResponse|RedirectResponse
    {
        if (! Config::get('comments.reactions.enabled', true)) {
            abort(404);
        }

        if (! Auth::check()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return Redirect::route('login');
        }

        $result = $this->commentService->react($request, $comment);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return Redirect::to(URL::previous().'#comment-'.$comment->getKey());
    }

    /**
     * Creates a reply to an existing comment.
     *
     * @throws Throwable
     */
    public function reply(Request $request, Comment $comment): RedirectResponse|CommentResource|JsonResponse
    {
        $reply = $this->commentService->reply($request, $comment);

        if ($request->wantsJson()) {
            return CommentResource::make($reply)
                ->response()
                ->setStatusCode(Config::get('comments.response_status.created', 201));
        }

        return Redirect::to(URL::previous().'#comment-'.$reply->getKey())
            ->with('success', Config::get('comments.response_messages.created'));
    }
}
