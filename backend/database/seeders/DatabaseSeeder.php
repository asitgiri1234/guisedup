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
     * Seed the application's database with a small, realistic dataset:
     * two named users, their posts, and cross-user interactions.
     */
    public function run(): void
    {
        $alice = User::factory()->create([
            'name' => 'Alice Example',
            'email' => 'alice@example.com',
        ]);

        $bob = User::factory()->create([
            'name' => 'Bob Example',
            'email' => 'bob@example.com',
        ]);

        $alicePosts = Post::factory(12)->for($alice)->create();
        $bobPosts = Post::factory(12)->for($bob)->create();

        // Each user engages with the other's posts so the feed has ranking signal.
        $bobPosts->each(function (Post $post) use ($alice): void {
            Interaction::factory()->for($alice)->for($post)->create();
        });

        $alicePosts->take(6)->each(function (Post $post) use ($bob): void {
            Interaction::factory()->for($bob)->for($post)->create();
        });
    }
}
