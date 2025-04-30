<?php

namespace Anil\Comments\Tests\TestSetup\Factories;

use Anil\Comments\Tests\TestSetup\Models\PostModel;
use Anil\Comments\Tests\TestSetup\Models\UserModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PostModel>
 */
class PostModelFactory extends Factory
{
    protected $model = PostModel::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
        ];
    }
}
