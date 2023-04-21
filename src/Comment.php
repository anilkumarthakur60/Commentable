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
use Illuminate\Support\Facades\Config;

class Comment extends Model
{
    use SoftDeletes;

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'commenter',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'comment', 'approved', 'guest_name', 'guest_email',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'approved' => 'boolean',
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => CommentCreated::class,
        'updated' => CommentUpdated::class,
        'deleted' => CommentDeleted::class,
    ];

    /**
     * The user who posted the comment.
     */
    public function commenter(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The model that was commented upon.
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Returns all comments that this comment is the parent of.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Config::get('comments.model'), 'child_id');
    }

    /**
     * Returns the comment to which this comment belongs to.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Config::get('comments.model'), 'child_id');
    }
}
