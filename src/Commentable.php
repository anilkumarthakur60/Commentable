<?php

namespace Anil\Comments;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
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

    /**
     * Get the latest comments for this model.
     */
    public function latestComments(int $limit = 5): Collection
    {
        return $this->comments()
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Get the most commented models.
     */
    public static function mostCommented(int $limit = 5): Collection
    {
        return static::withCount('comments')
            ->orderByDesc('comments_count')
            ->take($limit)
            ->get();
    }

    /**
     * Get comments with their replies in a nested structure.
     */
    public function commentsWithReplies(): Collection
    {
        return $this->comments()
            ->whereNull('child_id')
            ->with(['children', 'commenter'])
            ->get();
    }

    /**
     * Get the total number of comments for this model.
     */
    public function totalComments(): int
    {
        return $this->comments()->count();
    }

    /**
     * Get comments by a specific user.
     */
    public function commentsByUser(int $userId, string $commenterType): MorphMany
    {
        return $this->comments()->where('commenter_id', $userId)
            ->where('commenter_type', $commenterType);
    }

    /**
     * Get comments created within a specific date range.
     */
    public function commentsInDateRange(string $startDate, ?string $endDate = null): MorphMany
    {
        // if end is null, set it to the current date
        if ($endDate === null) {
            $endDate = $startDate;
        }

        return $this->comments()
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);
    }

    /**
     * Get comments with specific attributes.
     */
    public function commentsWithAttributes(array $attributes): MorphMany
    {
        return $this->comments()->where($attributes);
    }

    /**
     * Get comments with their related data.
     */
    public function commentsWithRelations(array $relations): MorphMany
    {
        return $this->comments()->with($relations);
    }
}
