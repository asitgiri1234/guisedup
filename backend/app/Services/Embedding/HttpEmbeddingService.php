<?php

namespace App\Services\Embedding;

use App\Services\Contracts\EmbeddingService;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Real embedding implementation: delegates to the Python FastAPI service
 * (Sentence Transformers) over HTTP. This is the Phase 3 replacement for
 * {@see MockEmbeddingService}, swapped in via config('embedding.driver').
 */
class HttpEmbeddingService implements EmbeddingService
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly int $dimensions,
        private readonly int $timeout = 10,
    ) {}

    public function embed(string $text): array
    {
        $response = Http::timeout($this->timeout)
            ->acceptJson()
            ->asJson()
            ->post(rtrim($this->baseUrl, '/').'/embed', ['texts' => [$text]]);

        $response->throw();

        $vector = $response->json('embeddings.0');

        if (! is_array($vector) || count($vector) !== $this->dimensions) {
            throw new RuntimeException(sprintf(
                'Embedding service returned an unexpected vector (expected %d dimensions, got %s).',
                $this->dimensions,
                is_array($vector) ? (string) count($vector) : gettype($vector),
            ));
        }

        return array_map(static fn ($value): float => (float) $value, $vector);
    }

    public function dimensions(): int
    {
        return $this->dimensions;
    }
}
