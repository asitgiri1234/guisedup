<?php

namespace App\Services\Ranking\Signals;

use App\Models\Post;
use App\Services\Ranking\RankingContext;
use App\Services\Ranking\RankingSignal;

/**
 * Exponential recency decay: a brand-new post scores ~1.0 and the score halves
 * roughly every `tau * ln(2)` hours.
 */
class TimeDecaySignal implements RankingSignal
{
    public function __construct(private readonly float $tauHours = 72.0) {}

    public function key(): string
    {
        return 'time_decay';
    }

    public function score(Post $post, RankingContext $context): float
    {
        $createdAt = $post->created_at;
        if ($createdAt === null) {
            return 0.0;
        }

        $ageHours = max(0.0, ($context->now->getTimestamp() - $createdAt->getTimestamp()) / 3600.0);

        return exp(-$ageHours / max(0.0001, $this->tauHours));
    }
}
