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
 * @property int|string    $id
 * @property string        $comment
 * @property bool          $approved
 * @property string|null   $guest_name
 * @property string|null   $guest_email
 * @property string        $commentable_type
 * @property int|string    $commentable_id
 * @property int|string|null $child_id
 * @property int|string|null $commenter_id
 * @property string|null   $commenter_type
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read Model|null $commenter
 * @property-read Model|null $commentable
 */
class Comment extends Model
{
    use SoftDeletes;

    /**
     * The relations to eager load on every query.
     *
     * @var list<string>
     */
    protected $with = ['commenter'];

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
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => CommentCreated::class,
        'updated' => CommentUpdated::class,
        'deleted' => CommentDeleted::class,
    ];

    /**
     * The user who posted the comment.
     *
     * @return MorphTo<Model, $this>
     */
    public function commenter(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The model that was commented upon.
     *
     * @return MorphTo<Model, $this>
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Returns all direct replies to this comment.
     *
     * @return HasMany<Comment, $this>
     */
    public function children(): HasMany
    {
        /** @var class-string<Comment> $commentModel */
        $commentModel = config('comments.model');

        return $this->hasMany(
            related: $commentModel,
            foreignKey: 'child_id',
            localKey: 'id'
        );
    }

    /**
     * Returns the parent comment this comment is a reply to.
     *
     * @return BelongsTo<Comment, $this>
     */
    public function parent(): BelongsTo
    {
        /** @var class-string<Comment> $commentModel */
        $commentModel = config('comments.model');

        return $this->belongsTo(
            related: $commentModel,
            foreignKey: 'child_id',
            ownerKey: 'id'
        );
    }

    /**
     * Returns the Gravatar URL for the comment author.
     *
     * Falls back to the "mystery person" avatar (d=mp) when no email is found.
     */
    public function getAvatarUrl(int $size = 64): string
    {
        $email = '';
        $commenter = $this->commenter;
        if ($commenter !== null) {
            $email = (string) $commenter->getAttribute('email');
        } elseif ($this->guest_email !== null) {
            $email = $this->guest_email;
        }

        $hash = md5(strtolower(trim($email)));

        return "https://www.gravatar.com/avatar/{$hash}.jpg?s={$size}&d=mp";
    }

    /**
     * Returns the display name for the comment author.
     */
    public function getAuthorName(): string
    {
        $commenter = $this->commenter;
        if ($commenter !== null) {
            return (string) $commenter->getAttribute('name');
        }

        if ($this->guest_name !== null) {
            return $this->guest_name;
        }

        $translation = __('comments::comments.anonymous');

        return is_string($translation) ? $translation : 'Anonymous';
    }

    /**
     * Whether this comment was posted by a guest (unauthenticated user).
     */
    public function isGuestComment(): bool
    {
        return $this->commenter_id === null;
    }
}
