<?php

namespace Tests\Unit\Ranking;

use App\Models\Post;
use App\Services\Ranking\RankingContext;
use App\Services\Ranking\Signals\TimeDecaySignal;
use Tests\TestCase;

class TimeDecaySignalTest extends TestCase
{
    private function contextNow(): RankingContext
    {
        return new RankingContext(
            viewerId: 1,
            followedAuthorIds: [],
            interactionCountsByAuthor: [],
            profileVector: null,
            now: now(),
        );
    }

    public function test_a_brand_new_post_scores_near_one(): void
    {
        $post = new Post;
        $post->created_at = now();

        $score = (new TimeDecaySignal(72.0))->score($post, $this->contextNow());

        $this->assertEqualsWithDelta(1.0, $score, 1e-3);
    }

    public function test_a_post_one_tau_old_decays_to_about_e_inverse(): void
    {
        $post = new Post;
        $post->created_at = now()->subHours(72);

        $score = (new TimeDecaySignal(72.0))->score($post, $this->contextNow());

        $this->assertEqualsWithDelta(exp(-1), $score, 1e-2);
    }

    public function test_newer_posts_outscore_older_posts(): void
    {
        $signal = new TimeDecaySignal(72.0);
        $context = $this->contextNow();

        $newer = tap(new Post, fn (Post $p) => $p->created_at = now()->subHours(1));
        $older = tap(new Post, fn (Post $p) => $p->created_at = now()->subHours(240));

        $this->assertGreaterThan(
            $signal->score($older, $context),
            $signal->score($newer, $context),
        );
    }
}
