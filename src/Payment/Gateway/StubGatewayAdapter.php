<?php

declare(strict_types=1);

namespace NenePayout\Payment\Gateway;

/**
 * Placeholder gateway for development before a real adapter (e.g. Stripe) is
 * wired. It creates a session reference and echoes the return URL, but contacts
 * no processor and settles nothing — settlement still arrives via webhook in a
 * later slice. Never use in production.
 */
final readonly class StubGatewayAdapter implements PaymentGatewayInterface
{
    public function createCharge(ChargeRequest $request): ChargeResult
    {
        return new ChargeResult(
            gatewayReference: 'stub_' . $request->paymentExecutionId,
            redirectUrl: $request->returnUrl,
            clientToken: null,
        );
    }
}
