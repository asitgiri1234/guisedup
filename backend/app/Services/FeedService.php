<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FeedService
{
    /** Weight applied to a post's (log-scaled) engagement. */
    private const POPULARITY_WEIGHT = 1.0;

    /** Penalty per hour of age, giving fresher posts a lift. */
    private const RECENCY_WEIGHT = 0.05;

    public const PER_PAGE = 20;

    /**
     * Return the ranked, paginated feed.
     *
     * Ranking is a transparent blend of engagement and recency:
     *
     *   score = POPULARITY_WEIGHT * ln(1 + interactions)
     *         - RECENCY_WEIGHT    * age_in_hours
     *
     * This is intentionally simple and explainable for Phase 2. Phase 3 will
     * layer personalised, embedding-based relevance on top of this baseline.
     */
    public function paginate(int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        // The engagement count is inlined as a correlated subquery rather than
        // reusing the withCount() alias: PostgreSQL only resolves output aliases
        // in ORDER BY when they stand alone, not inside a larger expression.
        $engagement = '(select count(*) from interactions where interactions.post_id = posts.id)';

        $score = '('.self::POPULARITY_WEIGHT.' * ln(1 + '.$engagement.')) '
            .'- ('.self::RECENCY_WEIGHT.' * (EXTRACT(EPOCH FROM (now() - posts.created_at)) / 3600.0))';

        return Post::query()
            ->with('user')
            ->withCount('interactions')
            ->orderByRaw($score.' DESC')
            ->orderByDesc('id')
            ->paginate($perPage);
    }
}
