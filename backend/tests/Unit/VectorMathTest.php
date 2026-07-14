<?php

namespace Tests\Unit;

use App\Support\VectorMath;
use PHPUnit\Framework\TestCase;

class VectorMathTest extends TestCase
{
    public function test_cosine_of_identical_vectors_is_one(): void
    {
        $this->assertEqualsWithDelta(1.0, VectorMath::cosine([1, 2, 3], [1, 2, 3]), 1e-9);
    }

    public function test_cosine_of_orthogonal_vectors_is_zero(): void
    {
        $this->assertEqualsWithDelta(0.0, VectorMath::cosine([1, 0], [0, 1]), 1e-9);
    }

    public function test_cosine_of_opposite_vectors_is_minus_one(): void
    {
        $this->assertEqualsWithDelta(-1.0, VectorMath::cosine([1, 1], [-1, -1]), 1e-9);
    }

    public function test_cosine_with_a_zero_vector_is_zero(): void
    {
        $this->assertSame(0.0, VectorMath::cosine([0, 0], [1, 1]));
    }

    public function test_mean_is_element_wise_average(): void
    {
        $this->assertSame([2.0, 3.0], VectorMath::mean([[1, 2], [3, 4]]));
    }

    public function test_mean_of_no_vectors_is_null(): void
    {
        $this->assertNull(VectorMath::mean([]));
    }
}
