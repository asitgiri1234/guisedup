<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Services\Ranking\FeedRanker;
use App\Services\Ranking\RankingContext;
use App\Support\VectorMath;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Builds a personalised, ranked feed for a viewer.
 *
 * A bounded candidate set is pulled from the database, then scored in-memory by
 * the reusable {@see FeedRanker} (authenticity + relationship depth + semantic
 * similarity + time decay) and paginated.
 */
class FeedService
{
    public function __construct(private readonly FeedRanker $ranker) {}

    public function paginate(User $viewer, ?int $perPage = null): LengthAwarePaginator
    {
        $perPage ??= (int) config('feed.per_page', 20);

        $candidates = Post::query()
            ->with('user')
            ->withCount('interactions')
            ->where('user_id', '!=', $viewer->id)
            ->whereNotNull('embedding')
            ->latest()
            ->limit((int) config('feed.candidate_limit', 500))
            ->get();

        $ranked = $this->ranker->rank($candidates, $this->contextFor($viewer));

        return $this->paginateCollection($ranked, $perPage);
    }

    /**
     * Precompute everything the signals need about this viewer.
     */
    private function contextFor(User $viewer): RankingContext
    {
        $followedAuthorIds = $viewer->following()->pluck('users.id')->all();

        /** @var array<int, int> $interactionCounts */
        $interactionCounts = DB::table('interactions')
            ->join('posts', 'posts.id', '=', 'interactions.post_id')
            ->where('interactions.user_id', $viewer->id)
            ->groupBy('posts.user_id')
            ->selectRaw('posts.user_id as author_id, count(*) as interactions')
            ->pluck('interactions', 'author_id')
            ->map(static fn ($count): int => (int) $count)
            ->all();

        return new RankingContext(
            viewerId: $viewer->id,
            followedAuthorIds: array_map('intval', $followedAuthorIds),
            interactionCountsByAuthor: $interactionCounts,
            profileVector: $this->profileVectorFor($viewer),
            now: now(),
        );
    }

    /**
     * The viewer's taste vector: the mean embedding of the posts they've
     * engaged with. Null when they have no engagement history yet.
     *
     * @return list<float>|null
     */
    private function profileVectorFor(User $viewer): ?array
    {
        $vectors = Post::query()
            ->whereNotNull('embedding')
            ->whereIn('id', function ($query) use ($viewer): void {
                $query->select('post_id')
                    ->from('interactions')
                    ->where('user_id', $viewer->id);
            })
            ->get(['id', 'embedding'])
            ->map(static fn (Post $post): array => $post->embedding->toArray())
            ->all();

        return VectorMath::mean($vectors);
    }

    /**
     * Wrap an already-ordered collection in a length-aware paginator.
     *
     * @param  Collection<int, Post>  $items
     */
    private function paginateCollection(Collection $items, int $perPage): LengthAwarePaginator
    {
        $page = Paginator::resolveCurrentPage();

        return new Paginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ],
        );
    }
}
