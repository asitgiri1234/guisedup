<?php

namespace App\Services\Ranking;

use App\Models\Post;

/**
 * A single, self-contained ranking signal.
 *
 * Signals are pure: they read only from the already-loaded post, its relations
 * and the {@see RankingContext}. They never touch the database, which keeps
 * them fast, composable and trivially unit-testable.
 */
interface RankingSignal
{
    /**
     * Stable key used to look up this signal's weight in config('feed.weights').
     */
    public function key(): string;

    /**
     * Score this post for the given viewer context, normalised to [0, 1].
     */
    public function score(Post $post, RankingContext $context): float;
}
