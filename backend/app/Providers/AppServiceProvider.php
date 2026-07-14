<?php

namespace App\Providers;

use App\Services\Contracts\EmbeddingService;
use App\Services\Embedding\HttpEmbeddingService;
use App\Services\Embedding\MockEmbeddingService;
use App\Services\Ranking\FeedRanker;
use App\Services\Ranking\Signals\AuthenticitySignal;
use App\Services\Ranking\Signals\RelationshipDepthSignal;
use App\Services\Ranking\Signals\SemanticSimilaritySignal;
use App\Services\Ranking\Signals\TimeDecaySignal;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerEmbeddingService();
        $this->registerFeedRanker();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Resolve the embedding implementation from config. Tests use the
     * deterministic "mock"; production uses "http" (the Python service).
     */
    private function registerEmbeddingService(): void
    {
        $this->app->bind(EmbeddingService::class, function (): EmbeddingService {
            $dimensions = (int) config('embedding.dimensions', 384);

            return match (config('embedding.driver')) {
                'http' => new HttpEmbeddingService(
                    baseUrl: (string) config('embedding.url'),
                    dimensions: $dimensions,
                    timeout: (int) config('embedding.timeout', 10),
                ),
                default => new MockEmbeddingService($dimensions),
            };
        });
    }

    /**
     * Assemble the reusable feed ranker from its weighted signals.
     */
    private function registerFeedRanker(): void
    {
        $this->app->singleton(FeedRanker::class, function (): FeedRanker {
            $config = config('feed');

            return new FeedRanker(
                signals: [
                    new AuthenticitySignal(),
                    new RelationshipDepthSignal(),
                    new SemanticSimilaritySignal(),
                    new TimeDecaySignal((float) ($config['time_decay_tau_hours'] ?? 72)),
                ],
                weights: $config['weights'] ?? [],
            );
        });
    }
}
