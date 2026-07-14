<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PostCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_post(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/posts', [
            'caption' => 'A crisp autumn layered look',
            'image_url' => 'https://example.com/outfit.jpg',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.caption', 'A crisp autumn layered look')
            ->assertJsonPath('data.image_url', 'https://example.com/outfit.jpg')
            ->assertJsonPath('data.author.id', $user->id);

        $this->assertDatabaseHas('posts', [
            'caption' => 'A crisp autumn layered look',
            'user_id' => $user->id,
        ]);
    }

    public function test_created_post_has_an_embedding_of_the_configured_dimension(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/posts', ['caption' => 'embed me'])->assertCreated();

        $post = Post::firstOrFail();
        $this->assertNotNull($post->embedding);
        $this->assertCount(config('embedding.dimensions'), $post->embedding->toArray());
    }

    public function test_caption_is_required(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/posts', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('caption');
    }

    public function test_guests_cannot_create_posts(): void
    {
        $this->postJson('/api/posts', ['caption' => 'nope'])
            ->assertUnauthorized();

        $this->assertDatabaseCount('posts', 0);
    }
}
