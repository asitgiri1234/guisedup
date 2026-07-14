<?php

namespace App\Http\Resources;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Post
 */
class PostResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'caption' => $this->caption,
            'image_url' => $this->image_url,
            'interactions_count' => $this->whenCounted('interactions'),
            'author' => new UserResource($this->whenLoaded('user')),
            // Present only on the ranked feed, for transparency/debugging.
            'ranking_score' => $this->when(
                isset($this->ranking_score),
                fn () => round((float) $this->ranking_score, 4),
            ),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
