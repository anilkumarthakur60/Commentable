<?php

namespace Anil\Comments;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Config;

/**
 * Add this trait to your User model to access a user's comments.
 *
 * @mixin Model
 */
trait Commenter
{
    /**
     * Returns all comments posted by this user.
     *
     * @return MorphMany<Comment, $this>
     */
    public function comments(): MorphMany
    {
        /** @var class-string<Comment> $model */
        $model = Config::get('comments.model');

        return $this->morphMany($model, 'commenter');
    }

    /**
     * Returns only approved (or unapproved) comments posted by this user.
     *
     * @return MorphMany<Comment, $this>
     */
    public function approvedComments(bool $approved = true): MorphMany
    {
        return $this->comments()->where('approved', $approved);
    }

    /**
     * Scope: filter users who have at least one approved (or unapproved) comment.
     *
     * Usage: User::approvedComments()->get()
     *        User::approvedComments(false)->get()  // users with pending comments
     *
     * @param  Builder<Model&Commenter>  $builder
     * @return Builder<Model&Commenter>
     */
    public function scopeApprovedComments(Builder $builder, bool $approved = true): Builder
    {
        return $builder->whereHas(
            'comments',
            static function (Builder $query) use ($approved): void {
                $query->where('approved', $approved);
            }
        );
    }
}
