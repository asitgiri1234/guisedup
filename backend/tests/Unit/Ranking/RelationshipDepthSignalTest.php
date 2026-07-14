<?php

namespace Tests\Unit\Ranking;

use App\Models\Post;
use App\Services\Ranking\RankingContext;
use App\Services\Ranking\Signals\RelationshipDepthSignal;
use Tests\TestCase;

class RelationshipDepthSignalTest extends TestCase
{
    private function postByAuthor(int $authorId): Post
    {
        $post = new Post();
        $post->user_id = $authorId;

        return $post;
    }

    private function context(array $followed, array $interactionCounts): RankingContext
    {
        return new RankingContext(1, $followed, $interactionCounts, null, now());
    }

    public function test_following_an_author_scores_higher_than_not_following(): void
    {
        $signal = new RelationshipDepthSignal();
        $post = $this->postByAuthor(42);

        $following = $signal->score($post, $this->context([42], []));
        $stranger = $signal->score($post, $this->context([], []));

        $this->assertGreaterThan($stranger, $following);
        $this->assertSame(0.0, $stranger);
    }

    public function test_more_past_interactions_increase_the_score(): void
    {
        $signal = new RelationshipDepthSignal();
        $post = $this->postByAuthor(42);

        $few = $signal->score($post, $this->context([], [42 => 1]));
        $many = $signal->score($post, $this->context([], [42 => 20]));

        $this->assertGreaterThan($few, $many);
    }

    public function test_score_stays_within_the_unit_interval(): void
    {
        $signal = new RelationshipDepthSignal();
        $post = $this->postByAuthor(42);

        $score = $signal->score($post, $this->context([42], [42 => 1000]));

        $this->assertGreaterThanOrEqual(0.0, $score);
        $this->assertLessThanOrEqual(1.0, $score);
    }
}
