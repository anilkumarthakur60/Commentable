<?php

namespace Anil\Comments\Exceptions;

use RuntimeException;

class MaxDepthExceededException extends RuntimeException
{
    public function __construct(int $maxDepth)
    {
        parent::__construct("Comment nesting depth cannot exceed {$maxDepth} levels.");
    }
}
