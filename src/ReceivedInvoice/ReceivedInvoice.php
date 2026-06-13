<?php

declare(strict_types=1);

namespace NenePayout\ReceivedInvoice;

/**
 * A received vendor invoice (受取請求書). `amount` is integer minimum currency
 * units. `taxBreakdown` is a recorded copy for input-tax-credit hand-off — Payout
 * does not compute deductions (ADR 0014). Soft-voided, never hard-deleted.
 */
final readonly class ReceivedInvoice
{
    /**
     * @param list<array{tax_rate_bps: int, taxable_amount: int, tax_amount: int}> $taxBreakdown
     */
    public function __construct(
        public string $vendorId,
        public int $amount,
        public string $dueDate,
        public string $status,
        public array $taxBreakdown = [],
        public ?string $organizationId = null,
        public ?string $registrationNumber = null,
        public ?string $vaultDocumentUrl = null,
        public ?string $id = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }
}
