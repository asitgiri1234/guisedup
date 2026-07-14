<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_feed_requires_authentication(): void
    {
        $this->getJson('/api/feed')->assertUnauthorized();
    }

    public function test_feed_is_paginated_at_twenty_per_page(): void
    {
        $author = User::factory()->create();
        Post::factory(25)->for($author)->create();

        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/feed')
            ->assertOk()
            ->assertJsonCount(20, 'data')
            ->assertJsonPath('meta.per_page', 20)
            ->assertJsonPath('meta.total', 25)
            ->assertJsonStructure([
                'data' => [['id', 'caption', 'author' => ['id', 'name'], 'interactions_count']],
                'links',
                'meta',
            ]);
    }

    public function test_second_page_returns_the_remainder(): void
    {
        $author = User::factory()->create();
        Post::factory(25)->for($author)->create();

        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/feed?page=2')
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.current_page', 2);
    }
}
