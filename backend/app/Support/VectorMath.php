<?php

namespace App\Support;

/**
 * Small, dependency-free vector helpers used by the ranking signals.
 */
class VectorMath
{
    /**
     * Cosine similarity of two equal-length vectors, in [-1, 1].
     * Returns 0.0 if either vector is zero-length or empty.
     *
     * @param  list<float>  $a
     * @param  list<float>  $b
     */
    public static function cosine(array $a, array $b): float
    {
        $length = min(count($a), count($b));
        if ($length === 0) {
            return 0.0;
        }

        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0; $i < $length; $i++) {
            $dot += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }

        if ($normA <= 0.0 || $normB <= 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }

    /**
     * Element-wise mean of a set of vectors, or null if there are none.
     *
     * @param  list<list<float>>  $vectors
     * @return list<float>|null
     */
    public static function mean(array $vectors): ?array
    {
        $vectors = array_values(array_filter(
            $vectors,
            static fn ($v): bool => is_array($v) && count($v) > 0,
        ));

        if ($vectors === []) {
            return null;
        }

        $dimensions = count($vectors[0]);
        $sum = array_fill(0, $dimensions, 0.0);

        foreach ($vectors as $vector) {
            for ($i = 0; $i < $dimensions; $i++) {
                $sum[$i] += (float) ($vector[$i] ?? 0.0);
            }
        }

        $count = count($vectors);

        return array_map(static fn (float $s): float => $s / $count, $sum);
    }
}
