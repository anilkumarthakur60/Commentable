<?php

namespace Anil\Comments;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Spatie\Honeypot\ProtectAgainstSpam;

abstract class CommentController extends Controller implements CommentControllerInterface
{
    public function __construct()
    {
        $this->middleware(Config::get('comments.middleware'));

        if (Config::get('comments.guest_commenting')) {
            $this->middleware(Config::get('comments.middleware'))->except('store');
            $this->middleware(ProtectAgainstSpam::class)->only('store');
        } else {
            $this->middleware(Config::get('comments.middleware'));
        }
    }
}
