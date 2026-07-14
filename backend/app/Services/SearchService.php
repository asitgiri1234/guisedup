<?php

namespace App\Services;

use App\Models\Post;
use App\Services\Contracts\EmbeddingService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SearchService
{
    public const PER_PAGE = 20;

    public function __construct(private readonly EmbeddingService $embeddings) {}

    /**
     * Semantic search: embed the query and return the nearest posts by cosine
     * distance, paginated.
     *
     * We order with an explicit `<=>` expression (rather than a select-based
     * neighbour helper) so the vector binding lives in the ORDER clause only —
     * this keeps pagination's COUNT query free of the vector parameter.
     */
    public function search(string $query, int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        $embedding = $this->embeddings->embed($query);
        $literal = '['.implode(',', $embedding).']';

        return Post::query()
            ->with('user')
            ->withEngagementCounts()
            ->whereNotNull('embedding')
            ->orderByRaw('embedding <=> ?::vector', [$literal])
            ->paginate($perPage);
    }
}
