<?php

namespace Database\Factories;

use App\Models\Image;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Image>
 */
class ImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'disk' => 'public',
            'path' => 'images/test/'.fake()->uuid().'.png',
            'original_filename' => 'test.png',
            'mime_type' => 'image/png',
            'size' => 1024,
            'width' => 100,
            'height' => 100,
        ];
    }
}
