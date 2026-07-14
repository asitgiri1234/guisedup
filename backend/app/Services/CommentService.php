<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CommentService
{
    public const PER_PAGE = 20;

    public function paginate(Post $post, int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        return $post->comments()
            ->with('user')
            ->latest()
            ->paginate($perPage);
    }

    public function create(User $author, Post $post, string $body): Comment
    {
        return $post->comments()->create([
            'user_id' => $author->id,
            'body' => $body,
        ])->load('user');
    }
}
