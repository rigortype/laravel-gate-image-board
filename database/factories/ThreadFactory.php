<?php

namespace Database\Factories;

use App\Enums\ThreadVisibility;
use App\Models\Board;
use App\Models\Thread;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
            'visibility' => ThreadVisibility::Team,
            'share_token' => null,
            'bumped_at' => now(),
        ];
    }

    /**
     * Indicate that the thread is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => ThreadVisibility::Public,
            'share_token' => null,
        ]);
    }

    /**
     * Indicate that the thread is unlisted and has a share token.
     */
    public function unlisted(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => ThreadVisibility::Unlisted,
            'share_token' => (string) Str::ulid(),
        ]);
    }

    /**
     * Indicate that the thread is private.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => ThreadVisibility::Private,
            'share_token' => null,
        ]);
    }
}
