<?php

namespace Tests\Unit;

use App\Services\Embedding\MockEmbeddingService;
use PHPUnit\Framework\TestCase;

class MockEmbeddingServiceTest extends TestCase
{
    public function test_it_produces_a_vector_of_the_requested_dimension(): void
    {
        $service = new MockEmbeddingService(384);

        $this->assertSame(384, $service->dimensions());
        $this->assertCount(384, $service->embed('summer beach outfit'));
    }

    public function test_it_is_deterministic_for_the_same_text(): void
    {
        $a = (new MockEmbeddingService(64))->embed('same text');
        $b = (new MockEmbeddingService(64))->embed('same text');

        $this->assertSame($a, $b);
    }

    public function test_different_text_produces_different_vectors(): void
    {
        $service = new MockEmbeddingService(64);

        $this->assertNotSame($service->embed('alpha'), $service->embed('beta'));
    }

    public function test_vectors_are_unit_length(): void
    {
        $vector = (new MockEmbeddingService(128))->embed('normalize me');
        $magnitude = sqrt(array_sum(array_map(static fn (float $v): float => $v * $v, $vector)));

        $this->assertEqualsWithDelta(1.0, $magnitude, 1e-6);
    }
}
