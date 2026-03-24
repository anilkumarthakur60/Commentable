<?php

namespace Anil\Comments\Tests\TestSetup\Models;

use Anil\Comments\Commenter;
use Anil\Comments\Tests\TestSetup\Factories\UserModelFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Request;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $email
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 *
 * @method static Builder<static> initializer(bool $orderBy = true)
 * @method static Builder<static> paginates(int $perPage = 15)
 * @method static Builder<static> simplePaginates(int $perPage = 15)
 * @method        Builder<static> initializer(bool $orderBy = true)
 * @method        Builder<static> paginates(int $perPage = 15)
 * @method        Builder<static> simplePaginates(int $perPage = 15)
 *
 * @mixin Builder<UserModel>
 */
class UserModel extends Authenticatable
{
    use Commenter;

    /** @use HasFactory<UserModelFactory> */
    use HasFactory;

    protected $table = 'users';

    protected string $guard_name = 'web1';

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'active',
    ];

    protected $casts = [
        'password'   => 'hashed',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return HasMany<PostModel,UserModel>
     */
    public function posts(): HasMany
    {
        /** @var HasMany<PostModel,UserModel> */
        return $this->hasMany(
            related: PostModel::class,
            foreignKey: 'user_id',
            localKey: 'id'
        );
    }

    /**
     * @param Builder<UserModel> $query
     *
     * @return Builder<UserModel>
     */
    public function scopeQueryFilter(Builder $query, mixed $search): Builder
    {
        $callable = [$query, 'likeWhere'];

        /** @var Builder<UserModel> */
        return is_callable($callable)
            ? call_user_func($callable, ['name', 'email'], $search)
            : $query;
    }

    /**
     * @param Builder<UserModel> $query
     *
     * @return Builder<UserModel>
     */
    public function scopeActive(Builder $query, int $active = 1): Builder
    {
        return $query->where('active', $active);
    }

    /**
     * @param Builder<UserModel> $query
     *
     * @return Builder<UserModel>
     */
    public function scopeStatus(Builder $query, int $status = 1): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * @param Builder<UserModel> $query
     *
     * @return Builder<UserModel>
     */
    public function scopeHasPosts(Builder $query): Builder
    {
        return $query->whereHas('posts');
    }

    public function afterCreateProcess(): static
    {
        $request = Request::instance();
        if ($request->has('post')) {
            /** @var array<string, mixed> $postData */
            $postData = $request->input('post');
            $this->posts()->create([
                'name'   => $postData['name'],
                'desc'   => $postData['desc'],
                'status' => $postData['status'],
                'active' => $postData['active'],
            ]);
        }

        return $this;
    }
}
