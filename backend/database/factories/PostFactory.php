<?php

namespace Database\Factories;

use App\Models\User;
use App\Services\Contracts\EmbeddingService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $caption = fake()->sentence(nbWords: 8);

        return [
            'user_id' => User::factory(),
            'caption' => $caption,
            'image_url' => fake()->imageUrl(),
            // Same contract the app uses — the mock in Phase 2, the real model later.
            'embedding' => app(EmbeddingService::class)->embed($caption),
        ];
    }
}
