<?php

namespace Tests\Unit\Ranking;

use App\Models\Post;
use App\Models\User;
use App\Services\Ranking\RankingContext;
use App\Services\Ranking\Signals\AuthenticitySignal;
use Tests\TestCase;

class AuthenticitySignalTest extends TestCase
{
    private function context(): RankingContext
    {
        return new RankingContext(1, [], [], null, now());
    }

    private function postByAuthorWithScore(float $score): Post
    {
        $author = new User(['authenticity_score' => $score]);
        $post = new Post();
        $post->setRelation('user', $author);

        return $post;
    }

    public function test_it_returns_the_authors_authenticity_score(): void
    {
        $signal = new AuthenticitySignal();

        $this->assertEqualsWithDelta(0.8, $signal->score($this->postByAuthorWithScore(0.8), $this->context()), 1e-9);
    }

    public function test_it_clamps_scores_into_the_unit_interval(): void
    {
        $signal = new AuthenticitySignal();

        $this->assertSame(1.0, $signal->score($this->postByAuthorWithScore(1.5), $this->context()));
        $this->assertSame(0.0, $signal->score($this->postByAuthorWithScore(-0.2), $this->context()));
    }
}
