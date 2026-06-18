<?php

declare(strict_types=1);

namespace NenePayout\Widget;

/**
 * The verified context carried by a widget token. The organization is derived
 * solely from the signed token (ADR 0018, 0021); never from the host or a
 * client parameter.
 */
final readonly class WidgetToken
{
    public function __construct(
        public string $organizationId,
    ) {
    }
}
