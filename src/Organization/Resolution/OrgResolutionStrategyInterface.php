<?php

declare(strict_types=1);

namespace NenePayout\Organization\Resolution;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Resolves an organization slug (or custom-domain identifier) from a request.
 * Returns null when this strategy cannot determine one (ADR 0018).
 */
interface OrgResolutionStrategyInterface
{
    public function resolve(ServerRequestInterface $request): ?string;
}
