<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SemanticSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_ranks_the_closest_post_first(): void
    {
        $author = User::factory()->create();

        // With deterministic embeddings, a post whose caption equals the query
        // embeds to the identical vector (cosine distance 0) and must rank first.
        $needle = 'unique turquoise linen safari jacket';
        $target = Post::factory()->for($author)->create(['caption' => $needle]);
        Post::factory(6)->for($author)->create();

        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/search?q='.urlencode($needle))
            ->assertOk()
            ->assertJsonPath('data.0.id', $target->id)
            ->assertJsonPath('data.0.caption', $needle);
    }

    public function test_cosine_search_returns_every_post_with_an_embedding(): void
    {
        $author = User::factory()->create();
        Post::factory(3)->for($author)->create();

        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/search?q=anything')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }
}
