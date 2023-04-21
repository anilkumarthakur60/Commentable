<?php

namespace Anil\Comments;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Config;

/**
 * Add this trait to your User model so
 * that you can retrieve the comments for a user.
 */
trait Commenter
{
    /**
     * Returns all comments that this user has made.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Config::get('comments.model'), 'commenter');
    }

    /**
     * Returns only approved comments that this user has made.
     */
    public function approvedComments(bool $approved = true)
    {
        return $this->comments()->where('approved', $approved);
    }

    /**
     * Returns only approved comments that this user has made.
     */
    public function scopeApprovedComments(Builder $builder, bool $approved = false)
    {
        if (! is_bool($approved)) {
            return $builder->comments();
        }

        return $builder->comments()->where('approved', $approved);
    }
}
