<?php

namespace Tests\Unit\Ranking;

use App\Models\Post;
use App\Services\Ranking\RankingContext;
use App\Services\Ranking\Signals\SemanticSimilaritySignal;
use Tests\TestCase;

class SemanticSimilaritySignalTest extends TestCase
{
    private function postWithEmbedding(array $vector): Post
    {
        $post = new Post();
        $post->embedding = $vector; // Vector cast serialises on set

        return $post;
    }

    private function context(?array $profile): RankingContext
    {
        return new RankingContext(1, [], [], $profile, now());
    }

    public function test_it_is_neutral_when_the_viewer_has_no_profile(): void
    {
        $signal = new SemanticSimilaritySignal();

        $score = $signal->score($this->postWithEmbedding([1, 0, 0]), $this->context(null));

        $this->assertSame(0.5, $score);
    }

    public function test_aligned_embeddings_score_higher_than_opposed_ones(): void
    {
        $signal = new SemanticSimilaritySignal();
        $profile = [1.0, 0.0, 0.0];

        $aligned = $signal->score($this->postWithEmbedding([1.0, 0.0, 0.0]), $this->context($profile));
        $opposed = $signal->score($this->postWithEmbedding([-1.0, 0.0, 0.0]), $this->context($profile));

        $this->assertEqualsWithDelta(1.0, $aligned, 1e-6);
        $this->assertEqualsWithDelta(0.0, $opposed, 1e-6);
        $this->assertGreaterThan($opposed, $aligned);
    }
}
