<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use App\Services\Contracts\EmbeddingService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'caption' => fake()->sentence(nbWords: 8),
            'image_url' => fake()->imageUrl(),
            // Derived from the FINAL caption (including create() overrides) via the
            // same contract the app uses — the mock in tests, the real model in prod.
            'embedding' => fn (array $attributes) => app(EmbeddingService::class)->embed($attributes['caption']),
        ];
    }
}
