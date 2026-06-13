<?php

declare(strict_types=1);

namespace NenePayout\Organization\Resolution;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Resolves the org slug from a fixed value (`ORG_SLUG`).
 *
 * For Tier A single-tenant installs and local dev where one organization owns
 * the whole instance. Returns null when no slug is configured.
 */
final readonly class EnvResolutionStrategy implements OrgResolutionStrategyInterface
{
    public function __construct(
        private string $orgSlug,
    ) {
    }

    public function resolve(ServerRequestInterface $request): ?string
    {
        return $this->orgSlug !== '' ? $this->orgSlug : null;
    }
}
