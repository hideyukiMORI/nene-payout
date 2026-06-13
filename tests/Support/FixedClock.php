<?php

declare(strict_types=1);

namespace NenePayout\Tests\Support;

use DateTimeImmutable;
use Nene2\Http\ClockInterface;

/**
 * Deterministic clock for tests (ADR 0012 — time is injectable).
 */
final readonly class FixedClock implements ClockInterface
{
    private DateTimeImmutable $now;

    public function __construct(string $iso = '2026-06-13T00:00:00+00:00')
    {
        $this->now = new DateTimeImmutable($iso);
    }

    public function now(): DateTimeImmutable
    {
        return $this->now;
    }
}
