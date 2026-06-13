<?php

declare(strict_types=1);

namespace NenePayout\Organization\Resolution;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Resolves the org slug from a URL path prefix: `/org1/api/...` → `org1`.
 *
 * For shared-host deployments without wildcard subdomains. Infrastructure paths
 * (health, auth, superadmin org management) resolve to null.
 */
final readonly class PathPrefixResolutionStrategy implements OrgResolutionStrategyInterface
{
    /** @var list<string> */
    private const BYPASS_PREFIXES = [
        '/health',
        '/api/v1/auth/',
        '/api/v1/organizations',
    ];

    public function resolve(ServerRequestInterface $request): ?string
    {
        $path = $request->getUri()->getPath();

        foreach (self::BYPASS_PREFIXES as $bypass) {
            if (str_starts_with($path, $bypass)) {
                return null;
            }
        }

        $parts = explode('/', ltrim($path, '/'), 2);
        $candidate = $parts[0];

        return $candidate !== '' ? $candidate : null;
    }
}
