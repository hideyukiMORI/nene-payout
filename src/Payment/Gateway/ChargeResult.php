<?php

declare(strict_types=1);

namespace NenePayout\Payment\Gateway;

/**
 * Result of creating a hosted charge session. Either `redirectUrl` (hosted
 * redirect) or `clientToken` (processor-hosted iframe) is returned for the
 * client to complete card entry. Settlement arrives later via webhook.
 */
final readonly class ChargeResult
{
    public function __construct(
        public string $gatewayReference,
        public ?string $redirectUrl = null,
        public ?string $clientToken = null,
    ) {
    }
}
