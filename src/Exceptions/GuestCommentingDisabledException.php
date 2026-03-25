<?php

namespace Anil\Comments\Exceptions;

use RuntimeException;

class GuestCommentingDisabledException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Guest commenting is disabled. Authentication is required to post a comment.');
    }
}
