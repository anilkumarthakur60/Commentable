<?php

namespace Anil\Comments\Services;

use Anil\Comments\Contracts\CommentServiceContract;
use Anil\Comments\Exceptions\GuestCommentingDisabledException;
use Anil\Comments\Exceptions\MaxDepthExceededException;
use Anil\Comments\Http\Requests\ReplyCommentRequest;
use Anil\Comments\Http\Requests\StoreCommentRequest;
use Anil\Comments\Http\Requests\UpdateCommentRequest;
use Anil\Comments\Models\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Throwable;

class CommentService implements CommentServiceContract
{
    /**
     * Handles creating a new comment for a given model.
     *
     * @throws GuestCommentingDisabledException
     * @throws Throwable
     */
    public function store(StoreCommentRequest $request): Comment
    {
        if (! $request->user() && ! Config::get('comments.guest_commenting')) {
            throw new GuestCommentingDisabledException;
        }

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
    public function update(UpdateCommentRequest $request, Comment $comment): Comment
    {
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
     * @throws MaxDepthExceededException
     * @throws Throwable
     */
    public function reply(ReplyCommentRequest $request, Comment $comment): Comment
    {
        $this->enforceMaxDepth($comment);

        /** @var class-string<Comment> $commentClass */
        $commentClass = Config::get('comments.model');

        return DB::transaction(function () use ($request, $comment, $commentClass): Comment {
            /** @var Comment $reply */
            $reply = new $commentClass;
            $reply->commenter()->associate($request->user());
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

    /**
     * Calculate the depth of a comment in the thread and enforce the max depth.
     *
     * @throws MaxDepthExceededException
     */
    protected function enforceMaxDepth(Comment $comment): void
    {
        /** @var int $maxDepth */
        $maxDepth = Config::get('comments.max_depth', 3);

        $depth = 0;
        $current = $comment;

        while ($current->child_id !== null) {
            $depth++;
            /** @var Comment|null $parent */
            $parent = $current->parent;
            if ($parent === null) {
                break;
            }
            $current = $parent;
        }

        if ($depth >= $maxDepth) {
            throw new MaxDepthExceededException($maxDepth);
        }
    }
}
