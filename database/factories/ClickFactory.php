<?php

namespace Database\Factories;

use App\Models\Click;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Click>
 */
class ClickFactory extends Factory
{
    protected $model = Click::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->numberBetween(1, 100),
            'url_id' => fake()->numberBetween(1, 100),
            'clicked_at' => now(),
            'ip' => fake()->ipv4(),
            'country' => fake()->countryCode(),
            'referrer' => fake()->url(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
