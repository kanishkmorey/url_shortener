<?php

namespace Database\Factories;

use App\Models\Url;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Url>
 */
class UrlFactory extends Factory
{
    protected $model = Url::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => fake()->numberBetween(1, 100),
            'url' => fake()->url(),
            'short_code' => fake()->lexify('??????'),
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(10),
            'is_active' => true,
            'is_blocked' => false,
            'meta' => [],
        ];
    }
}
