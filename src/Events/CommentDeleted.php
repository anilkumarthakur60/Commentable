<?php

namespace Anil\Comments\Events;

use Anil\Comments\Models\Comment;
use Illuminate\Queue\SerializesModels;

class CommentDeleted
{
    use SerializesModels;

    public function __construct(
        public readonly Comment $comment
    ) {
    }
}
