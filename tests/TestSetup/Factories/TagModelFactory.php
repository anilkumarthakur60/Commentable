<?php

namespace Anil\Comments\Tests\TestSetup\Factories;

use Anil\Comments\Tests\TestSetup\Models\TagModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TagModel>
 */
class TagModelFactory extends Factory
{
    protected $model = TagModel::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->name,
        ];
    }
}
