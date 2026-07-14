<?php

namespace App\Services\Ranking\Signals;

use App\Models\Post;
use App\Services\Ranking\RankingContext;
use App\Services\Ranking\RankingSignal;

/**
 * Rewards posts from authentic creators. Reads the author's authenticity_score
 * (already in [0, 1]); the viewer context is irrelevant here.
 */
class AuthenticitySignal implements RankingSignal
{
    public function key(): string
    {
        return 'authenticity';
    }

    public function score(Post $post, RankingContext $context): float
    {
        $score = (float) ($post->user->authenticity_score ?? 0.5);

        return max(0.0, min(1.0, $score));
    }
}
