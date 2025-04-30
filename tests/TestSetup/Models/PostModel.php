<?php

namespace Anil\Comments\Tests\TestSetup\Models;

use Anil\Comments\Commentable;
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
}
