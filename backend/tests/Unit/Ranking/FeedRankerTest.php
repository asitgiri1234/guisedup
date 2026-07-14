<?php

namespace Tests\Unit\Ranking;

use App\Models\Post;
use App\Services\Ranking\FeedRanker;
use App\Services\Ranking\RankingContext;
use App\Services\Ranking\RankingSignal;
use Illuminate\Support\Collection;
use Tests\TestCase;

class FeedRankerTest extends TestCase
{
    /**
     * A stub signal that simply reads a per-post attribute, so composition can
     * be tested without depending on any real signal's maths.
     */
    private function stubSignal(string $attribute, string $key): RankingSignal
    {
        return new class($attribute, $key) implements RankingSignal
        {
            public function __construct(private string $attribute, private string $key) {}

            public function key(): string
            {
                return $this->key;
            }

            public function score(Post $post, RankingContext $context): float
            {
                return (float) $post->getAttribute($this->attribute);
            }
        };
    }

    private function context(): RankingContext
    {
        return new RankingContext(1, [], [], null, now());
    }

    public function test_score_is_the_weighted_sum_of_signals(): void
    {
        $ranker = new FeedRanker(
            [$this->stubSignal('a', 'a'), $this->stubSignal('b', 'b')],
            ['a' => 0.25, 'b' => 0.75],
        );

        $post = new Post;
        $post->a = 0.4;
        $post->b = 0.8;

        // 0.25*0.4 + 0.75*0.8 = 0.1 + 0.6 = 0.7
        $this->assertEqualsWithDelta(0.7, $ranker->scoreFor($post, $this->context()), 1e-9);
    }

    public function test_rank_orders_posts_best_first_and_attaches_the_score(): void
    {
        $ranker = new FeedRanker([$this->stubSignal('s', 's')], ['s' => 1.0]);

        $low = tap(new Post(['caption' => 'low']), fn (Post $p) => $p->s = 0.1);
        $high = tap(new Post(['caption' => 'high']), fn (Post $p) => $p->s = 0.9);
        $mid = tap(new Post(['caption' => 'mid']), fn (Post $p) => $p->s = 0.5);

        $ranked = $ranker->rank(new Collection([$low, $high, $mid]), $this->context());

        $this->assertSame(['high', 'mid', 'low'], $ranked->pluck('caption')->all());
        $this->assertEqualsWithDelta(0.9, $ranked->first()->ranking_score, 1e-9);
    }
}
