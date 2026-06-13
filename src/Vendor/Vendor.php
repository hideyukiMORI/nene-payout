<?php

declare(strict_types=1);

namespace NenePayout\Vendor;

/**
 * A payout vendor (支払先) with bank account details. `accountType` is one of
 * `普通` / `当座` (terms.md §5). Soft-deactivated, never hard-deleted (ADR 0013).
 */
final readonly class Vendor
{
    public function __construct(
        public string $name,
        public string $bankCode,
        public string $branchCode,
        public string $accountType,
        public string $accountNumber,
        public string $accountName,
        public bool $isActive = true,
        public ?string $organizationId = null,
        public ?string $registrationNumber = null,
        public ?string $id = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }
}
