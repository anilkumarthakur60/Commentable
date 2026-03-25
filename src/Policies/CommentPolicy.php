<?php

namespace Anil\Comments\Policies;

use Anil\Comments\Models\Comment;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Config;

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
     * Users detected as admin via the configured attribute may delete any comment.
     */
    public function delete(User $user, Comment $comment): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return $user->getKey() === $comment->commenter_id;
    }

    /**
     * A user may reply to a comment.
     *
     * Self-reply behaviour is controlled by the 'allow_self_reply' config key.
     */
    public function reply(User $user, Comment $comment): bool
    {
        if (!Config::get('comments.allow_self_reply', false)) {
            return $user->getKey() !== $comment->commenter_id;
        }

        return true;
    }

    /**
     * Check whether the user is considered an administrator.
     *
     * Uses the attribute name from config ('admin_attribute').
     * Returns false when admin_attribute is set to null.
     */
    protected function isAdmin(User $user): bool
    {
        /** @var string|null $attribute */
        $attribute = Config::get('comments.admin_attribute', 'is_admin');

        if ($attribute === null) {
            return false;
        }

        return (bool) $user->getAttribute($attribute);
    }
}
