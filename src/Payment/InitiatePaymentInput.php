<?php

declare(strict_types=1);

namespace NenePayout\Payment;

final readonly class InitiatePaymentInput
{
    public function __construct(
        public string $receivedInvoiceId,
        public string $gateway,
        public ?string $returnUrl = null,
    ) {
    }
}
