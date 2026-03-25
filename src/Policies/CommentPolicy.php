<?php

namespace Anil\Comments\Policies;

use Anil\Comments\Models\Comment;
use Illuminate\Foundation\Auth\User;

class CommentPolicy
{
    /**
     * Any authenticated user may post a comment.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * A user may edit their own comment.
     */
    public function update(User $user, Comment $comment): bool
    {
        return $user->getKey() === $comment->commenter_id;
    }

    /**
     * A user may delete their own comment.
     *
     * Users with an `is_admin` attribute set to true may delete any comment.
     */
    public function delete(User $user, Comment $comment): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return $user->getKey() === $comment->commenter_id;
    }

    /**
     * A user may reply to any comment that is not their own.
     */
    public function reply(User $user, Comment $comment): bool
    {
        return $user->getKey() !== $comment->commenter_id;
    }

    /**
     * Check whether the user is considered an administrator.
     *
     * Override this policy or add your own to customise admin detection.
     */
    protected function isAdmin(User $user): bool
    {
        return (bool) $user->getAttribute('is_admin');
    }
}
