<?php

namespace Anil\Comments\Tests\TestSetup\Models;

use Anil\Comments\Commenter;
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
 * @property-read int $status
 * @property-read int $active
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read Carbon $deleted_at
 *
 * @method static Builder<Model> initializer(bool $orderBy = true)
 * @method static Builder<Model> paginates(int $perPage = 15)
 * @method static Builder<Model> simplePaginates(int $perPage = 15)
 * @method static Builder<Model> likeWhere(array<string> $attributes, ?string $searchTerm = null)
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
        'password' => 'hashed',
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
     * @param  Builder<UserModel>  $query
     * @return Builder<UserModel>
     */
    public function scopeQueryFilter(Builder $query, mixed $search): Builder
    {
        return $query->likeWhere(
            attributes: ['name', 'email'],
            searchTerm: $search
        );
    }

    /**
     * @param  Builder<UserModel>  $query
     * @return Builder<UserModel>
     */
    public function scopeActive(Builder $query, int $active = 1): Builder
    {
        return $query->where('active', $active);
    }

    /**
     * @param  Builder<UserModel>  $query
     * @return Builder<UserModel>
     */
    public function scopeStatus(Builder $query, int $status = 1): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * @param  Builder<UserModel>  $query
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
                'name' => $postData['name'],
                'desc' => $postData['desc'],
                'status' => $postData['status'],
                'active' => $postData['active'],
            ]);
        }

        return $this;
    }
}
