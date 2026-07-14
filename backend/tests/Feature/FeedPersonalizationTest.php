<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FeedPersonalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_feed_excludes_the_viewers_own_posts(): void
    {
        $viewer = User::factory()->create();
        Post::factory(3)->for($viewer)->create();
        Post::factory(2)->for(User::factory())->create();

        Sanctum::actingAs($viewer);

        $response = $this->getJson('/api/feed')->assertOk()->assertJsonCount(2, 'data');

        $ownIds = $viewer->posts()->pluck('id')->all();
        foreach ($response->json('data') as $post) {
            $this->assertNotContains($post['id'], $ownIds);
        }
    }

    public function test_a_followed_author_outranks_a_stranger_all_else_equal(): void
    {
        $viewer = User::factory()->create();
        $followed = User::factory()->create(['authenticity_score' => 0.7]);
        $stranger = User::factory()->create(['authenticity_score' => 0.7]);

        $viewer->following()->attach($followed);

        $followedPost = Post::factory()->for($followed)->create();
        $strangerPost = Post::factory()->for($stranger)->create();

        Sanctum::actingAs($viewer);

        $response = $this->getJson('/api/feed')->assertOk();

        $ids = Collection::make($response->json('data'))->pluck('id');
        $this->assertLessThan(
            $ids->search($strangerPost->id),
            $ids->search($followedPost->id),
            'Followed author post should rank ahead of the stranger.',
        );
    }

    public function test_feed_exposes_a_ranking_score(): void
    {
        Post::factory(2)->for(User::factory())->create();

        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/feed')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'ranking_score']]]);
    }
}
