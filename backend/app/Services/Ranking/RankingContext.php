<?php

namespace App\Services\Ranking;

use Carbon\CarbonInterface;

/**
 * Everything a signal needs about the viewer, precomputed once per feed request.
 */
class RankingContext
{
    /**
     * @param  list<int>  $followedAuthorIds  Authors the viewer follows.
     * @param  array<int, int>  $interactionCountsByAuthor  author_id => #interactions by viewer.
     * @param  list<float>|null  $profileVector  The viewer's taste vector (mean of engaged posts), or null.
     */
    public function __construct(
        public readonly int $viewerId,
        public readonly array $followedAuthorIds,
        public readonly array $interactionCountsByAuthor,
        public readonly ?array $profileVector,
        public readonly CarbonInterface $now,
    ) {
    }

    public function follows(int $authorId): bool
    {
        return in_array($authorId, $this->followedAuthorIds, true);
    }

    public function interactionsWith(int $authorId): int
    {
        return $this->interactionCountsByAuthor[$authorId] ?? 0;
    }
}
