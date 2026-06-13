<?php

declare(strict_types=1);

namespace NenePayout\Support;

/**
 * 適格請求書発行事業者 登録番号. Syntax-only validation (`T` + 13 digits) — it does
 * NOT prove the number exists or is registered (ADR 0014).
 */
final class RegistrationNumber
{
    public static function isValid(string $value): bool
    {
        return preg_match('/^T[0-9]{13}$/', $value) === 1;
    }
}
