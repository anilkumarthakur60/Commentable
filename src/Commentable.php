<?php

namespace Anil\Comments;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Config;

/**
 * Add this trait to any model that you want to be able to
 * comment upon or get comments for.
 */
trait Commentable
{
    /**
     * This static method does voodoo magic to
     * delete leftover comments once the commentable
     * model is deleted.
     */
    protected static function bootCommentable(): void
    {
        static::deleted(function ($commentable) {
            foreach ($commentable->comments as $comment) {
                $comment->delete();
            }
        });
    }

    /**
     * Returns all comments for this model.
     *
     * @return MorphMany<Comment, Commentable>
     */
    public function comments(): MorphMany
    {
        /** @var MorphMany<Comment, Commentable> */
        return $this->morphMany(Config::get('comments.model'), 'commentable');
    }

    /**
     * Returns only approved comments for this model.
     *
     * @return MorphMany<Comment, Commentable>
     */
    public function approvedComments(bool $approved = true): MorphMany
    {
        /** @var MorphMany<Comment, Commentable> */
        return $this->comments()->where('approved', $approved);
    }
}
