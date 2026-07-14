<?php

namespace App\Services\Ranking;

use App\Models\Post;
use Illuminate\Support\Collection;

/**
 * Composes weighted {@see RankingSignal}s into a single personalised score and
 * ranks a candidate set of posts. Reusable and framework-light: give it signals
 * and weights and it will score anything.
 */
class FeedRanker
{
    /**
     * @param  list<RankingSignal>  $signals
     * @param  array<string, float>  $weights  keyed by signal->key()
     */
    public function __construct(
        private readonly array $signals,
        private readonly array $weights,
    ) {}

    /**
     * The blended score for a single post: sum of weight * signal.
     */
    public function scoreFor(Post $post, RankingContext $context): float
    {
        $total = 0.0;

        foreach ($this->signals as $signal) {
            $total += ($this->weights[$signal->key()] ?? 0.0) * $signal->score($post, $context);
        }

        return $total;
    }

    /**
     * Attach a `ranking_score` to each post and return them best-first.
     *
     * @param  Collection<int, Post>  $posts
     * @return Collection<int, Post>
     */
    public function rank(Collection $posts, RankingContext $context): Collection
    {
        return $posts
            ->each(fn (Post $post) => $post->ranking_score = $this->scoreFor($post, $context))
            ->sortByDesc('ranking_score')
            ->values();
    }

    /**
     * @return list<RankingSignal>
     */
    public function signals(): array
    {
        return $this->signals;
    }
}
