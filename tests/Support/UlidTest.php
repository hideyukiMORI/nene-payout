<?php

declare(strict_types=1);

namespace NenePayout\Tests\Support;

use NenePayout\Support\Ulid;
use PHPUnit\Framework\TestCase;

final class UlidTest extends TestCase
{
    public function test_has_canonical_length_and_alphabet(): void
    {
        $ulid = Ulid::generate();

        self::assertSame(26, strlen($ulid));
        self::assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $ulid);
    }

    public function test_is_unique_across_calls(): void
    {
        self::assertNotSame(Ulid::generate(), Ulid::generate());
    }

    public function test_is_time_sortable(): void
    {
        $earlier = Ulid::generate(1_000_000);
        $later = Ulid::generate(2_000_000);

        self::assertLessThan(0, strcmp($earlier, $later));
    }
}
