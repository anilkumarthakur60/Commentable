<?php

namespace Anil\Comments\Tests\TestSetup\Models;

use Anil\Comments\Commentable;
use Anil\Comments\Tests\TestSetup\Factories\PostModelFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 *
 * @mixin Builder<PostModel>
 */
class PostModel extends Model
{
    use Commentable;

    /** @use HasFactory<PostModelFactory> */
    use HasFactory;

    protected $table = 'posts';

    protected $fillable = [
        'name',
    ];
    // hasApprovedComments

    public function hasApprovedComments(): bool
    {
        return $this->comments()->where('approved', true)->exists();
    }

    public function hasComments(): int
    {
        return $this->comments()->count();
    }

    public function approvedCommentsCount(): int
    {
        return $this->comments()->where('approved', true)->count();
    }
}
