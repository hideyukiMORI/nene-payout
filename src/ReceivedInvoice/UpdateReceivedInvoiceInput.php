<?php

declare(strict_types=1);

namespace NenePayout\ReceivedInvoice;

final readonly class UpdateReceivedInvoiceInput
{
    /**
     * @param list<array{tax_rate_bps: int, taxable_amount: int, tax_amount: int}> $taxBreakdown
     */
    public function __construct(
        public string $vendorId,
        public int $amount,
        public string $dueDate,
        public array $taxBreakdown = [],
        public ?string $registrationNumber = null,
        public ?string $vaultDocumentUrl = null,
    ) {
    }
}
