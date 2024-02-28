<?php

namespace Anil\Comments;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class CommentService
{
    /**
     * Handles creating a new comment for given model.
     *
     * @throws Exception
     *
     * @return mixed the configured comment-model
     */
    public function store(Request $request)
    {
        // If guest commenting is turned off, authorize this action.
        if (!Config::get('comments.guest_commenting')) {
            Gate::authorize('create-comment', Comment::class);
        }

        // Define guest rules if user is not logged in.
        if (!Auth::check()) {
            $guest_rules = [
                'guest_name'  => 'required|string|max:255',
                'guest_email' => 'required|string|email|max:255',
            ];
        }

        // Merge guest rules, if any, with normal validation rules.
        Validator::make($request->all(), array_merge($guest_rules ?? [], [
            'commentable_type' => 'required|string',
            'commentable_id'   => 'required|string|min:1',
            'message'          => 'required|string',
        ]))->validate();

        $model = $request->commentable_type::findOrFail($request->commentable_id);

        $commentClass = Config::get('comments.model');

        try {
            DB::beginTransaction();
            $comment = new $commentClass();

            if (!Auth::check()) {
                $comment->guest_name = $request->guest_name;
                $comment->guest_email = $request->guest_email;
            } else {
                $comment->commenter()->associate(Auth::user());
            }

            $comment->commentable()->associate($model);
            $comment->comment = $request->message;
            $comment->approved = !Config::get('comments.approval_required');
            $comment->save();

            if (method_exists($comment, 'afterCreateProcess')) {
                $model->afterCreateProcess();
            }

            DB::commit();

            return $comment;
        } catch (Exception $exception) {
            DB::rollBack();

            throw new Exception($exception);
        }
    }

    /**
     * Handles updating the message of the comment.
     *
     * @throws Exception
     *
     * @return Comment the configured comment-model
     */
    public function update(Request $request, Comment $comment)
    {
        Gate::authorize('edit-comment', $comment);

        Validator::make($request->all(), [
            'message' => 'required|string',
        ])->validate();

        try {
            DB::beginTransaction();
            $comment->update([
                'comment' => $request->message,
            ]);

            if (method_exists($comment, 'afterUpdateProcess')) {
                $comment->afterUpdateProcess();
            }

            DB::commit();

            return $comment;
        } catch (Exception $exception) {
            throw new Exception($exception);
        }
    }

    /**
     * Handles deleting a comment.
     *
     * @throws Exception
     *
     * @return mixed the configured comment-model
     */
    public function destroy(Comment $comment): void
    {
        Gate::authorize('delete-comment', $comment);

        try {
            DB::beginTransaction();
            if (method_exists($comment, 'beforeDeleteProcess')) {
                $comment->beforeDeleteProcess();
            }

            if (Config::get('comments.soft_deletes')) {
                $comment->delete();
            } else {
                $comment->forceDelete();
            }

            if (method_exists($comment, 'afterDeleteProcess')) {
                $comment->afterDeleteProcess();
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw new Exception($e);
        }
    }

    /**
     * Handles creating a reply "comment" to a comment.
     *
     * @throws Exception
     *
     * @return mixed the configured comment-model
     */
    public function reply(Request $request, Comment $comment)
    {
        Gate::authorize('reply-to-comment', $comment);

        Validator::make($request->all(), [
            'message' => 'required|string',
        ])->validate();

        $commentClass = Config::get('comments.model');

        try {
            DB::beginTransaction();
            $reply = new $commentClass();
            $reply->commenter()->associate(Auth::user());
            $reply->commentable()->associate($comment->commentable);
            $reply->parent()->associate($comment);
            $reply->comment = $request->message;
            $reply->approved = !Config::get('comments.approval_required');
            $reply->save();

            if (method_exists($reply, 'afterReplyProcess')) {
                $reply->afterReplyProcess();
            }

            DB::commit();

            return $reply;
        } catch (Exception $exception) {
            DB::rollBack();

            throw new Exception($exception);
        }
    }
}
