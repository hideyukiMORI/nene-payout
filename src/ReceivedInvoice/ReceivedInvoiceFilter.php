<?php

declare(strict_types=1);

namespace NenePayout\ReceivedInvoice;

/**
 * Read-side filter. Null fields do not constrain. When `status` is null, voided
 * invoices are excluded; set `status` explicitly to include a specific state.
 * `dueFrom` / `dueTo` are inclusive ISO date bounds.
 */
final readonly class ReceivedInvoiceFilter
{
    public function __construct(
        public ?string $status = null,
        public ?string $vendorId = null,
        public ?string $dueFrom = null,
        public ?string $dueTo = null,
    ) {
    }
}
