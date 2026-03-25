<?php

namespace Anil\Comments\Tests\Support\Factories;

use Anil\Comments\Tests\Support\Models\PostModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PostModel>
 */
class PostModelFactory extends Factory
{
    protected $model = PostModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
        ];
    }
}
