<?php

namespace App\Providers;

use App\Services\Contracts\EmbeddingService;
use App\Services\Embedding\MockEmbeddingService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Resolve the embedding implementation from config so Phase 3 can swap
        // in the real (HTTP → Python service) driver without touching callers.
        $this->app->bind(EmbeddingService::class, function (): EmbeddingService {
            return match (config('embedding.driver')) {
                // 'http' => new HttpEmbeddingService(...), // Phase 3
                default => new MockEmbeddingService((int) config('embedding.dimensions', 384)),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
