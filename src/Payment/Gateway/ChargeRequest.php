<?php

declare(strict_types=1);

namespace NenePayout\Payment\Gateway;

/**
 * Instruction to create a hosted card-payment session. Carries no card data —
 * the PAN is entered on the gateway's hosted page/iframe (ADR 0010).
 */
final readonly class ChargeRequest
{
    public function __construct(
        public string $organizationId,
        public string $receivedInvoiceId,
        public string $paymentExecutionId,
        public int $amount,
        public ?string $returnUrl = null,
    ) {
    }
}
