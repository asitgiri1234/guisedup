<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Services\Contracts\EmbeddingService;

class PostService
{
    public function __construct(private readonly EmbeddingService $embeddings)
    {
    }

    /**
     * Create a post for the given author and attach its embedding.
     *
     * The embedding is produced through the {@see EmbeddingService} contract;
     * in Phase 2 that is the deterministic mock, in Phase 3 the real model.
     *
     * @param  array{caption: string, image_url?: string|null}  $data
     */
    public function create(User $author, array $data): Post
    {
        return $author->posts()->create([
            'caption' => $data['caption'],
            'image_url' => $data['image_url'] ?? null,
            'embedding' => $this->embeddings->embed($data['caption']),
        ]);
    }
}
