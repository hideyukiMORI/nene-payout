<?php

declare(strict_types=1);

namespace NenePayout\Payment;

/** Read-side filter. Null fields do not constrain. Org scope is applied by the repository. */
final readonly class PaymentExecutionFilter
{
    public function __construct(
        public ?string $status = null,
        public ?string $receivedInvoiceId = null,
    ) {
    }
}
