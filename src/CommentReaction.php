<?php

namespace Anil\Comments;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int|string $id
 * @property int|string $comment_id
 * @property int|string $reactor_id
 * @property string $reactor_type
 * @property string $type like|dislike
 */
class CommentReaction extends Model
{
    protected $fillable = [
        'comment_id',
        'reactor_id',
        'reactor_type',
        'type',
    ];

    /** @return BelongsTo<Comment, $this> */
    public function comment(): BelongsTo
    {
        /** @var class-string<Comment> $model */
        $model = config('comments.model');

        return $this->belongsTo($model);
    }

    /** @return MorphTo<Model, $this> */
    public function reactor(): MorphTo
    {
        return $this->morphTo();
    }
}
