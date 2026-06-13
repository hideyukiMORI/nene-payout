<?php

declare(strict_types=1);

namespace NenePayout\Tests\Support;

use NenePayout\Support\Ulid;
use PHPUnit\Framework\TestCase;

final class UlidTest extends TestCase
{
    public function test_has_canonical_length_and_crockford_alphabet(): void
    {
        for ($i = 0; $i < 200; $i++) {
            $ulid = Ulid::generate();
            self::assertSame(26, strlen($ulid));
            // Crockford base32 excludes I, L, O, U.
            self::assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $ulid);
        }
    }

    public function test_is_unique_across_many_calls(): void
    {
        $set = [];
        for ($i = 0; $i < 1000; $i++) {
            $set[Ulid::generate()] = true;
        }

        self::assertCount(1000, $set);
    }

    public function test_is_time_sortable(): void
    {
        $earlier = Ulid::generate(1_000_000);
        $later = Ulid::generate(2_000_000);

        self::assertLessThan(0, strcmp($earlier, $later));
    }

    public function test_adjacent_timestamps_order_by_time_prefix(): void
    {
        $a = Ulid::generate(1_700_000_000_000);
        $b = Ulid::generate(1_700_000_000_001);

        self::assertLessThan(0, strcmp(substr($a, 0, 10), substr($b, 0, 10)));
    }

    public function test_zero_timestamp_yields_zero_time_prefix(): void
    {
        $ulid = Ulid::generate(0);

        self::assertSame('0000000000', substr($ulid, 0, 10));
        self::assertSame(26, strlen($ulid));
    }

    public function test_same_timestamp_differs_in_random_part(): void
    {
        $a = Ulid::generate(1_700_000_000_000);
        $b = Ulid::generate(1_700_000_000_000);

        self::assertSame(substr($a, 0, 10), substr($b, 0, 10));
        self::assertNotSame($a, $b);
    }
}
