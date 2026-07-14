<?php

namespace App\Services\Ranking\Signals;

use App\Models\Post;
use App\Services\Ranking\RankingContext;
use App\Services\Ranking\RankingSignal;

/**
 * How connected the viewer is to the author, combining an explicit follow with
 * the depth of past engagement (a follow you also interact with ranks higher
 * than a follow you ignore).
 */
class RelationshipDepthSignal implements RankingSignal
{
    public function __construct(
        private readonly float $followWeight = 0.5,
        private readonly float $interactionWeight = 0.5,
        private readonly float $interactionScale = 3.0,
    ) {
    }

    public function key(): string
    {
        return 'relationship';
    }

    public function score(Post $post, RankingContext $context): float
    {
        $authorId = (int) $post->user_id;

        $following = $context->follows($authorId) ? 1.0 : 0.0;
        // tanh squashes an unbounded count into [0, 1).
        $engagement = tanh($context->interactionsWith($authorId) / $this->interactionScale);

        $score = $this->followWeight * $following + $this->interactionWeight * $engagement;

        return max(0.0, min(1.0, $score));
    }
}
