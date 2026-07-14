<?php

namespace Tests\Feature;

use App\Services\Contracts\EmbeddingService;
use App\Services\Embedding\MockEmbeddingService;
use Tests\TestCase;

class EmbeddingServiceBindingTest extends TestCase
{
    public function test_contract_resolves_to_the_mock_in_phase_2(): void
    {
        $service = app(EmbeddingService::class);

        $this->assertInstanceOf(MockEmbeddingService::class, $service);
        $this->assertSame(config('embedding.dimensions'), $service->dimensions());
    }
}
