<?php

namespace Anil\Comments\Events;

use Anil\Comments\Models\Comment;
use Illuminate\Queue\SerializesModels;

class CommentCreated
{
    use SerializesModels;

    public function __construct(
        public readonly Comment $comment
    ) {
    }
}
