<?php

declare(strict_types=1);

namespace NenePayout\Payment\Gateway;

/**
 * The only entry point for charging (backend-standards.md). Adapters create a
 * hosted card-payment session; the PAN never reaches Payout (ADR 0010). Money
 * movement is the gateway's regulated function (ADR 0009).
 */
interface PaymentGatewayInterface
{
    public function createCharge(ChargeRequest $request): ChargeResult;
}
