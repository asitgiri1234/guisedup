<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_requires_authentication(): void
    {
        $this->getJson('/api/search?q=outfit')->assertUnauthorized();
    }

    public function test_search_requires_a_query(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/search')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('q');
    }

    public function test_search_returns_paginated_semantic_results(): void
    {
        $author = User::factory()->create();
        Post::factory(25)->for($author)->create();

        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/search?q=summer+layered+outfit')
            ->assertOk()
            ->assertJsonCount(20, 'data')
            ->assertJsonPath('meta.per_page', 20)
            ->assertJsonPath('meta.total', 25);
    }
}
