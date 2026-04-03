<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Workshop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Workshop>
 */
class WorkshopFactory extends Factory
{
    protected $model = Workshop::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+1 day', '+45 days');
        $end = (clone $start)->modify('+'.fake()->numberBetween(1, 4).' hours');

        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'starts_at' => $start,
            'ends_at' => $end,
            'capacity' => fake()->numberBetween(8, 40),
        ];
    }
}

