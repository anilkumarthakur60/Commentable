<?php

namespace Anil\Comments\Tests\Support\Factories;

use Anil\Comments\Tests\Support\Models\UserModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserModel>
 */
class UserModelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<UserModel>
     */
    protected $model = UserModel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('password123'),
        ];
    }
}
