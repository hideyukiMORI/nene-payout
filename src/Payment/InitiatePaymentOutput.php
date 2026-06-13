<?php

declare(strict_types=1);

namespace NenePayout\Payment;

final readonly class InitiatePaymentOutput
{
    public function __construct(
        public PaymentExecution $paymentExecution,
        public ?string $gatewayRedirectUrl = null,
        public ?string $clientToken = null,
    ) {
    }
}
