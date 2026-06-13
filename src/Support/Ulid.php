<?php

declare(strict_types=1);

namespace NenePayout\Support;

/**
 * Minimal ULID generator — 26-char Crockford base32, lexicographically sortable
 * by creation time (48-bit millisecond timestamp + 80-bit randomness). Used for
 * all primary keys (terms.md / domain-model.md).
 */
final class Ulid
{
    private const ALPHABET = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';

    /**
     * @param int|null $timeMs Millisecond timestamp; defaults to now (for tests/determinism).
     */
    public static function generate(?int $timeMs = null): string
    {
        $time = $timeMs ?? (int) (microtime(true) * 1000);

        // 48-bit time → 10 base32 chars, most-significant first.
        $timePart = '';
        for ($i = 0; $i < 10; $i++) {
            $timePart = self::ALPHABET[$time % 32] . $timePart;
            $time = intdiv($time, 32);
        }

        // 80-bit randomness → 16 base32 chars.
        $randPart = '';
        for ($i = 0; $i < 16; $i++) {
            $randPart .= self::ALPHABET[random_int(0, 31)];
        }

        return $timePart . $randPart;
    }
}
