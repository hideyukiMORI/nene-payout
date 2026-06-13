<?php

declare(strict_types=1);

namespace NenePayout\Payment;

/**
 * A card payment execution record (決済実行記録). `amount` is what the vendor is
 * owed; `chargeAmount` / `processingFee` are gateway-reported and stay null until
 * a signed-off fee accounting model exists (ADR 0015). Money is integer minimum
 * currency units. Terminal records are immutable (ADR 0013).
 */
final readonly class PaymentExecution
{
    public function __construct(
        public string $receivedInvoiceId,
        public int $amount,
        public string $gateway,
        public string $status,
        public ?string $organizationId = null,
        public ?int $chargeAmount = null,
        public ?int $processingFee = null,
        public ?string $gatewayReference = null,
        public ?string $id = null,
        public ?string $initiatedAt = null,
        public ?string $completedAt = null,
    ) {
    }
}
