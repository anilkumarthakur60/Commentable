<?php

namespace Anil\Comments\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Config;

/**
 * @property int|string $id
 * @property int|string $comment_id
 * @property int|string $reactor_id
 * @property string $reactor_type
 * @property string $type
 */
class CommentReaction extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'comment_id',
        'reactor_id',
        'reactor_type',
        'type',
    ];

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        /** @var string $table */
        $table = Config::get('comments.table_names.reactions', parent::getTable());

        return $table;
    }

    /**
     * The comment this reaction belongs to.
     *
     * @return BelongsTo<Comment, $this>
     */
    public function comment(): BelongsTo
    {
        /** @var class-string<Comment> $model */
        $model = Config::get('comments.model');

        return $this->belongsTo($model);
    }

    /**
     * The user who reacted.
     *
     * @return MorphTo<Model, $this>
     */
    public function reactor(): MorphTo
    {
        return $this->morphTo();
    }
}
