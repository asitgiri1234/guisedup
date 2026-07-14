<?php

namespace App\Services\Embedding;

use App\Services\Contracts\EmbeddingService;

/**
 * Deterministic, dependency-free stand-in for a real embedding model.
 *
 * It derives a stable unit vector from the SHA-256 digest of the input text,
 * so the same text always yields the same embedding. This is NOT semantically
 * meaningful — it exists purely so the storage, feed and semantic-search
 * plumbing can be built and tested before the real model lands in Phase 3.
 */
class MockEmbeddingService implements EmbeddingService
{
    public function __construct(private int $dimensions = 384)
    {
    }

    public function embed(string $text): array
    {
        // Expand the hash until we have at least one byte per dimension.
        $bytes = '';
        $counter = 0;
        while (strlen($bytes) < $this->dimensions) {
            $bytes .= hash('sha256', $text . '#' . $counter, true);
            $counter++;
        }

        // Map each byte [0,255] to a float in [-1, 1].
        $vector = [];
        for ($i = 0; $i < $this->dimensions; $i++) {
            $vector[] = (ord($bytes[$i]) - 127.5) / 127.5;
        }

        return $this->normalize($vector);
    }

    public function dimensions(): int
    {
        return $this->dimensions;
    }

    /**
     * Scale the vector to unit length so cosine distance behaves sensibly.
     *
     * @param  list<float>  $vector
     * @return list<float>
     */
    private function normalize(array $vector): array
    {
        $magnitude = sqrt(array_sum(array_map(static fn (float $v): float => $v * $v, $vector)));

        if ($magnitude <= 0.0) {
            return $vector;
        }

        return array_map(static fn (float $v): float => $v / $magnitude, $vector);
    }
}
