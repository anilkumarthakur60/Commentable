<?php

namespace Anil\Comments\Services;

use Anil\Comments\Contracts\CommentServiceContract;
use Anil\Comments\Models\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Throwable;

class CommentService implements CommentServiceContract
{
    /**
     * Handles creating a new comment for a given model.
     *
     * @throws Throwable
     */
    public function store(Request $request): Comment
    {
        if (! Config::get('comments.guest_commenting')) {
            Gate::authorize('create-comment', Comment::class);
        }

        $guestRules = [];
        if (! $request->user()) {
            $guestRules = [
                'guest_name' => Config::get('comments.validation.guest_name', ['required', 'string', 'max:255']),
                'guest_email' => Config::get('comments.validation.guest_email', ['required', 'string', 'email', 'max:255']),
            ];
        }

        Validator::make($request->all(), array_merge($guestRules, [
            'commentable_type' => ['required', 'string'],
            'commentable_id' => ['required', 'min:1'],
            'message' => Config::get('comments.validation.message', ['required', 'string']),
        ]))->validate();

        /** @var class-string<Model> $commentableClass */
        $commentableClass = $request->string('commentable_type')->toString();

        /** @var Model $model */
        $model = $commentableClass::query()->findOrFail($request->input('commentable_id'));

        /** @var class-string<Comment> $commentClass */
        $commentClass = Config::get('comments.model');

        return DB::transaction(function () use ($request, $model, $commentClass): Comment {
            /** @var Comment $comment */
            $comment = new $commentClass;

            if (! $request->user()) {
                $comment->guest_name = $request->string('guest_name')->toString();
                $comment->guest_email = $request->string('guest_email')->toString();
            } else {
                $comment->commenter()->associate($request->user());
            }

            $comment->commentable()->associate($model);
            $comment->comment = $request->string('message')->toString();
            $comment->approved = ! Config::get('comments.approval_required');
            $comment->save();

            if (method_exists($model, 'afterCreate')) {
                $model->afterCreate();
            }

            return $comment;
        });
    }

    /**
     * Handles updating the message of an existing comment.
     *
     * @throws Throwable
     */
    public function update(Request $request, Comment $comment): Comment
    {
        Gate::authorize('edit-comment', $comment);

        Validator::make($request->all(), [
            'message' => Config::get('comments.validation.message', ['required', 'string']),
        ])->validate();

        return DB::transaction(function () use ($request, $comment): Comment {
            $comment->update([
                'comment' => $request->string('message')->toString(),
            ]);

            if (method_exists($comment, 'afterUpdate')) {
                $comment->afterUpdate();
            }

            return $comment;
        });
    }

    /**
     * Handles deleting a comment (soft or hard based on config).
     *
     * @throws Throwable
     */
    public function destroy(Comment $comment): void
    {
        Gate::authorize('delete-comment', $comment);

        DB::transaction(function () use ($comment): void {
            if (method_exists($comment, 'beforeDelete')) {
                $comment->beforeDelete();
            }

            if (Config::get('comments.soft_deletes')) {
                $comment->delete();
            } else {
                $comment->forceDelete();
            }

            if (method_exists($comment, 'afterDelete')) {
                $comment->afterDelete();
            }
        });
    }

    /**
     * Handles creating a reply to an existing comment.
     *
     * @throws Throwable
     */
    public function reply(Request $request, Comment $comment): Comment
    {
        Gate::authorize('reply-to-comment', $comment);

        Validator::make($request->all(), [
            'message' => Config::get('comments.validation.message', ['required', 'string']),
        ])->validate();

        /** @var class-string<Comment> $commentClass */
        $commentClass = Config::get('comments.model');

        return DB::transaction(function () use ($request, $comment, $commentClass): Comment {
            /** @var Comment $reply */
            $reply = new $commentClass;
            $reply->commenter()->associate(Auth::user());
            $reply->commentable()->associate($comment->commentable);
            $reply->parent()->associate($comment);
            $reply->comment = $request->string('message')->toString();
            $reply->approved = ! Config::get('comments.approval_required');
            $reply->save();

            if (method_exists($reply, 'afterReply')) {
                $reply->afterReply();
            }

            return $reply;
        });
    }
}
