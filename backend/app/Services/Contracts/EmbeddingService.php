<?php

namespace App\Services\Contracts;

use App\Services\Embedding\MockEmbeddingService;

/**
 * Contract for turning text into a dense vector embedding.
 *
 * Phase 2 binds this to {@see MockEmbeddingService}.
 * Phase 3 will bind a real implementation that calls the Python service,
 * with no changes required in any consumer.
 */
interface EmbeddingService
{
    /**
     * Generate an embedding for the given text.
     *
     * @return list<float> A vector of length {@see self::dimensions()}.
     */
    public function embed(string $text): array;

    /**
     * The dimensionality of vectors produced by this service.
     */
    public function dimensions(): int;
}
