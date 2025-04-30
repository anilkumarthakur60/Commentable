<?php

namespace Anil\Comments;

use Anil\Comments\Events\CommentCreated;
use Anil\Comments\Events\CommentDeleted;
use Anil\Comments\Events\CommentUpdated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $comment
 * @property bool $approved
 * @property string $guest_name
 * @property string $guest_email
 * @property string $commentable_type
 * @property string $commentable_id
 * @property string $child_id
 * @property string $commenter_id
 */
class Comment extends Model
{
    use SoftDeletes;

    /**
     * The relations to an eager load on every query.
     *
     * @var list<string>
     */
    protected $with = [
        'commenter',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'comment',
        'approved',
        'guest_name',
        'guest_email',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'approved' => 'boolean',
    ];

    /**
     * The event map for the model.
     *
     * @var array<string, string>
     */
    protected $dispatchesEvents = [
        'created' => CommentCreated::class,
        'updated' => CommentUpdated::class,
        'deleted' => CommentDeleted::class,
    ];

    /**
     * The user who posted the comment
     *
     * @return MorphTo<Model, Comment>
     */
    public function commenter(): MorphTo
    {
        /** @var MorphTo<Model, Comment> */
        return $this->morphTo();
    }

    /**
     * The model that was commented upon.
     *
     * @return MorphTo<Model, Comment>
     */
    public function commentable(): MorphTo
    {
        /** @var MorphTo<Model, Comment> */
        return $this->morphTo();
    }

    /**
     * Returns all comments that this comment is the parent of.
     *
     * @return HasMany<Comment, Comment>
     */
    public function children(): HasMany
    {
        /**
         * @var class-string<Comment> $commentModel
         */
        $commentModel = config('comments.model');

        /** @var HasMany<Comment, Comment> */
        return $this->hasMany(
            related: $commentModel,
            foreignKey: 'child_id',
            localKey: 'id'
        );
    }

    /**
     * Returns the comment to which this comment belongs to.
     *
     * @return BelongsTo<Comment, Comment>
     */
    public function parent(): BelongsTo
    {
        /**
         * @var class-string<Comment> $commentModel
         */
        $commentModel = config('comments.model');

        /** @var BelongsTo<Comment, Comment> */
        return $this->belongsTo(
            related: $commentModel,
            foreignKey: 'child_id',
            ownerKey: 'id'
        );
    }
}
