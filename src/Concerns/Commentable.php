<?php

namespace Anil\Comments\Concerns;

use Anil\Comments\Models\Comment;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

/**
 * Add this trait to any Eloquent model that should support comments.
 *
 * @mixin Model
 */
trait Commentable
{
    /**
     * Get the models with the highest comment count.
     *
     * @return Collection<int, static>
     */
    public static function mostCommented(int $limit = 5): Collection
    {
        /** @var Collection<int, static> */
        return static::withCount('comments')
            ->orderByDesc('comments_count')
            ->take($limit)
            ->get();
    }

    /**
     * Delete all comments when the commentable model is deleted.
     */
    protected static function bootCommentable(): void
    {
        static::deleted(function (self $commentable): void {
            $commentable->comments()->each(function (Comment $comment): void {
                $comment->delete();
            });
        });
    }

    /**
     * Returns all comments for this model.
     *
     * @return MorphMany<Comment, $this>
     */
    public function comments(): MorphMany
    {
        /** @var class-string<Comment> $model */
        $model = Config::get('comments.model');

        return $this->morphMany($model, 'commentable');
    }

    /**
     * Returns only approved (or unapproved) comments for this model.
     *
     * @return MorphMany<Comment, $this>
     */
    public function approvedComments(bool $approved = true): MorphMany
    {
        return $this->comments()->where('approved', $approved);
    }

    /**
     * Get the $limit most recent comments for this model.
     *
     * @return Collection<int, Comment>
     */
    public function latestComments(int $limit = 5): Collection
    {
        /** @var Collection<int, Comment> */
        return $this->comments()
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Get top-level comments with their nested replies eager-loaded.
     *
     * @return Collection<int, Comment>
     */
    public function commentsWithReplies(): Collection
    {
        /** @var Collection<int, Comment> */
        return $this->comments()
            ->whereNull('child_id')
            ->with(['children', 'commenter'])
            ->get();
    }

    /**
     * Get paginated top-level comments with their replies eager-loaded.
     *
     * Uses the per_page value from config when no override is given.
     *
     * @return LengthAwarePaginator<Comment>
     */
    public function paginatedComments(?int $perPage = null): LengthAwarePaginator
    {
        $perPage ??= (int) Config::get('comments.per_page', 10);

        $sort = Config::get('comments.sort', 'latest');
        $orderDirection = $sort === 'oldest' ? 'asc' : 'desc';

        return $this->comments()
            ->whereNull('child_id')
            ->with(['children', 'commenter', 'reactions'])
            ->orderBy('created_at', $orderDirection)
            ->paginate($perPage);
    }

    /**
     * Get the total number of comments for this model.
     */
    public function totalComments(): int
    {
        return $this->comments()->count();
    }

    /**
     * Get comments posted by a specific commenter.
     *
     * @return MorphMany<Comment, $this>
     */
    public function commentsByUser(int|string $userId, string $commenterType): MorphMany
    {
        return $this->comments()
            ->where('commenter_id', $userId)
            ->where('commenter_type', $commenterType);
    }

    /**
     * Get comments created within a date range (inclusive).
     *
     * @return MorphMany<Comment, $this>
     */
    public function commentsInDateRange(string $startDate, ?string $endDate = null): MorphMany
    {
        $endDate ??= $startDate;

        return $this->comments()
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);
    }

    /**
     * Get comments matching specific column/value pairs.
     *
     * @param array<string, mixed> $attributes
     *
     * @return MorphMany<Comment, $this>
     */
    public function commentsWithAttributes(array $attributes): MorphMany
    {
        return $this->comments()->where($attributes);
    }

    /**
     * Get comments with the given relationships eager-loaded.
     *
     * @param array<int, string>|array<string, Closure> $relations
     *
     * @return MorphMany<Comment, $this>
     */
    public function commentsWithRelations(array $relations): MorphMany
    {
        return $this->comments()->with($relations);
    }
}
