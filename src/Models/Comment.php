<?php

namespace Anil\Comments\Models;

use Anil\Comments\Events\CommentCreated;
use Anil\Comments\Events\CommentDeleted;
use Anil\Comments\Events\CommentUpdated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

/**
 * @property int|string $id
 * @property string $comment
 * @property bool $approved
 * @property string|null $guest_name
 * @property string|null $guest_email
 * @property string $commentable_type
 * @property int|string $commentable_id
 * @property int|string|null $child_id
 * @property int|string|null $commenter_id
 * @property string|null $commenter_type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
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
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        /** @var string $table */
        $table = Config::get('comments.table_names.comments', parent::getTable());

        return $table;
    }

    /**
     * The event map for the model.
     *
     * Only dispatches events when enabled in config.
     *
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => CommentCreated::class,
        'updated' => CommentUpdated::class,
        'deleted' => CommentDeleted::class,
    ];

    /**
     * Fire the given event for the model.
     *
     * Skips event dispatching when events are disabled in config.
     *
     * @param  string  $event
     * @param  bool  $halt
     * @return mixed
     */
    protected function fireModelEvent($event, $halt = true)
    {
        if (! Config::get('comments.events.enabled', true)) {
            return true;
        }

        return parent::fireModelEvent($event, $halt);
    }

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
        $commentModel = Config::get('comments.model');

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
        $commentModel = Config::get('comments.model');

        return $this->belongsTo(
            related: $commentModel,
            foreignKey: 'child_id',
            ownerKey: 'id'
        );
    }

    /**
     * Returns the avatar URL for the comment author.
     *
     * Uses the provider configured in config/comments.php (default: gravatar).
     * Returns an empty string when the provider is disabled (null).
     */
    public function getAvatarUrl(?int $size = null): string
    {
        $provider = Config::get('comments.avatar.provider', 'gravatar');

        if ($provider === null) {
            return '';
        }

        /** @var int $configSize */
        $configSize = Config::get('comments.avatar.size', 64);
        $size ??= $configSize;

        /** @var string $default */
        $default = Config::get('comments.avatar.default', 'mp');

        $email = '';
        $commenter = $this->commenter;

        if ($commenter !== null) {
            $commenterEmail = $commenter->getAttribute('email');
            $email = is_string($commenterEmail) ? $commenterEmail : '';
        } elseif ($this->guest_email !== null) {
            $email = $this->guest_email;
        }

        $hash = md5(strtolower(trim($email)));

        return "https://www.gravatar.com/avatar/{$hash}.jpg?s={$size}&d={$default}";
    }

    /**
     * Returns the display name for the comment author.
     */
    public function getAuthorName(): string
    {
        $commenter = $this->commenter;

        if ($commenter !== null) {
            $name = $commenter->getAttribute('name');

            return is_string($name) ? $name : '';
        }

        if ($this->guest_name !== null) {
            return $this->guest_name;
        }

        $translation = __('comments::comments.anonymous');

        return is_string($translation) ? $translation : 'Anonymous';
    }

    /**
     * All reactions on this comment.
     *
     * Uses the reaction model from config so it can be swapped for a custom one.
     *
     * @return HasMany<CommentReaction, $this>
     */
    public function reactions(): HasMany
    {
        /** @var class-string<CommentReaction> $reactionModel */
        $reactionModel = Config::get('comments.reaction_model', CommentReaction::class);

        return $this->hasMany($reactionModel);
    }

    /**
     * Whether this comment was posted by a guest (unauthenticated user).
     */
    public function isGuestComment(): bool
    {
        return $this->commenter_id === null;
    }
}
