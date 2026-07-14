<?php

namespace Database\Seeders;

use App\Models\Interaction;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed a small, realistic dataset: named users with authenticity scores,
     * their posts, a follow graph, and cross-user interactions — enough signal
     * for the ranked, personalised feed to be meaningful.
     */
    public function run(): void
    {
        $alice = User::factory()->create([
            'name' => 'Alice Example',
            'email' => 'alice@example.com',
            'authenticity_score' => 0.95,
        ]);

        $bob = User::factory()->create([
            'name' => 'Bob Example',
            'email' => 'bob@example.com',
            'authenticity_score' => 0.70,
        ]);

        $alicePosts = Post::factory(12)->for($alice)->create();
        $bobPosts = Post::factory(12)->for($bob)->create();

        // Alice follows Bob; Bob follows Alice.
        $alice->following()->attach($bob);
        $bob->following()->attach($alice);

        // Each user engages with the other's posts so the feed has ranking signal.
        $bobPosts->each(function (Post $post) use ($alice): void {
            Interaction::factory()->for($alice)->for($post)->create();
        });

        $alicePosts->take(6)->each(function (Post $post) use ($bob): void {
            Interaction::factory()->for($bob)->for($post)->create();
        });
    }
}
