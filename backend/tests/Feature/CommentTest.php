<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_comment_on_a_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for(User::factory())->create();

        Sanctum::actingAs($user);

        $this->postJson("/api/posts/{$post->id}/comments", ['body' => 'Love this look!'])
            ->assertCreated()
            ->assertJsonPath('data.body', 'Love this look!')
            ->assertJsonPath('data.author.id', $user->id);

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'user_id' => $user->id,
            'body' => 'Love this look!',
        ]);
    }

    public function test_comment_body_is_required(): void
    {
        $post = Post::factory()->for(User::factory())->create();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/posts/{$post->id}/comments", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('body');
    }

    public function test_comments_are_listed_paginated(): void
    {
        $post = Post::factory()->for(User::factory())->create();
        Comment::factory(25)->for($post)->for(User::factory())->create();

        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/posts/{$post->id}/comments")
            ->assertOk()
            ->assertJsonCount(20, 'data')
            ->assertJsonPath('meta.total', 25)
            ->assertJsonStructure(['data' => [['id', 'body', 'author' => ['id', 'name']]]]);
    }

    public function test_guests_cannot_comment(): void
    {
        $post = Post::factory()->for(User::factory())->create();

        $this->postJson("/api/posts/{$post->id}/comments", ['body' => 'nope'])
            ->assertUnauthorized();

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_feed_exposes_reaction_and_comment_counts(): void
    {
        $post = Post::factory()->for(User::factory())->create();
        Comment::factory(2)->for($post)->for(User::factory())->create();

        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/feed')
            ->assertOk()
            ->assertJsonPath('data.0.comments_count', 2)
            ->assertJsonStructure(['data' => [['reactions' => ['like', 'fire', 'clap'], 'comments_count']]]);
    }
}
