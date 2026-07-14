<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InteractionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_log_an_interaction(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for(User::factory())->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/interactions', [
            'post_id' => $post->id,
            'type' => 'like',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.post_id', $post->id)
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.type', 'like');

        $this->assertDatabaseHas('interactions', [
            'user_id' => $user->id,
            'post_id' => $post->id,
            'type' => 'like',
        ]);
    }

    public function test_emoji_reaction_types_are_accepted(): void
    {
        $post = Post::factory()->for(User::factory())->create();
        Sanctum::actingAs(User::factory()->create());

        foreach (['like', 'fire', 'clap'] as $type) {
            $this->postJson('/api/interactions', ['post_id' => $post->id, 'type' => $type])
                ->assertCreated()
                ->assertJsonPath('data.type', $type);
        }

        $this->assertDatabaseCount('interactions', 3);
    }

    public function test_interaction_requires_an_existing_post(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/interactions', [
            'post_id' => 999999,
            'type' => 'like',
        ])->assertUnprocessable()->assertJsonValidationErrors('post_id');
    }

    public function test_interaction_type_must_be_valid(): void
    {
        $post = Post::factory()->for(User::factory())->create();

        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/interactions', [
            'post_id' => $post->id,
            'type' => 'teleport',
        ])->assertUnprocessable()->assertJsonValidationErrors('type');
    }

    public function test_guests_cannot_log_interactions(): void
    {
        $post = Post::factory()->for(User::factory())->create();

        $this->postJson('/api/interactions', [
            'post_id' => $post->id,
            'type' => 'like',
        ])->assertUnauthorized();

        $this->assertDatabaseCount('interactions', 0);
    }
}
