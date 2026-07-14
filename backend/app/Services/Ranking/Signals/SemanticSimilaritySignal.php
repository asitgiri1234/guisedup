<?php

namespace App\Services\Ranking\Signals;

use App\Models\Post;
use App\Services\Ranking\RankingContext;
use App\Services\Ranking\RankingSignal;
use App\Support\VectorMath;
use Pgvector\Vector;

/**
 * How close a post is to the viewer's taste vector (the mean embedding of the
 * posts they have engaged with). Cosine similarity in [-1, 1] is mapped to
 * [0, 1]; with no history yet, returns a neutral 0.5.
 */
class SemanticSimilaritySignal implements RankingSignal
{
    public function key(): string
    {
        return 'semantic';
    }

    public function score(Post $post, RankingContext $context): float
    {
        if ($context->profileVector === null) {
            return 0.5;
        }

        $embedding = $this->toArray($post->embedding);
        if ($embedding === null) {
            return 0.5;
        }

        $cosine = VectorMath::cosine($context->profileVector, $embedding);

        return max(0.0, min(1.0, ($cosine + 1.0) / 2.0));
    }

    /**
     * @return list<float>|null
     */
    private function toArray(mixed $embedding): ?array
    {
        if ($embedding instanceof Vector) {
            return $embedding->toArray();
        }

        return is_array($embedding) ? $embedding : null;
    }
}
