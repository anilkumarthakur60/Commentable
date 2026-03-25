<?php

namespace Anil\Comments\Exceptions;

use RuntimeException;

class CommentNotFoundException extends RuntimeException
{
    public function __construct(int|string $id)
    {
        parent::__construct("Comment with ID [{$id}] was not found.");
    }
}
