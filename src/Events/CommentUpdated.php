<?php

namespace Anil\Comments\Events;

use Anil\Comments\Comment;
use Illuminate\Queue\SerializesModels;

class CommentUpdated
{
    use SerializesModels;

    public $comment;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }
}
