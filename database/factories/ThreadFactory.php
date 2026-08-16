<?php

namespace Database\Factories;

use App\Models\Board;
use App\Models\Thread;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Thread>
 */
class ThreadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'board_id' => Board::factory(),
            'subject' => fake()->optional()->sentence(4),
            'bumped_at' => now(),
        ];
    }
}
