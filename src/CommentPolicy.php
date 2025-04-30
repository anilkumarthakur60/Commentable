<?php

namespace Anil\Comments;

use Illuminate\Foundation\Auth\User;

class CommentPolicy
{
    /**
     * Can a user create the comment?
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Can a user delete the comment?
     */
    public function delete(User $user, Comment $comment): bool
    {
        return $user->getKey() === $comment->commenter_id;
    }

    /**
     * Can a user update the comment?
     */
    public function update(User $user, Comment $comment): bool
    {
        return $user->getKey() === $comment->commenter_id;
    }

    /**
     * Can a user reply to the comment?
     */
    public function reply(User $user, Comment $comment): bool
    {
        return $user->getKey() !== $comment->commenter_id;
    }
}
