<?php

declare(strict_types=1);

namespace NenePayout\Organization\Resolution;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Resolves by full host (custom/vanity domain): the raw Host header is looked up
 * via OrganizationRepository::findByCustomDomain() by OrgResolverMiddleware.
 */
final readonly class CustomDomainResolutionStrategy implements OrgResolutionStrategyInterface
{
    public function resolve(ServerRequestInterface $request): ?string
    {
        $host = $request->getUri()->getHost();

        if (str_contains($host, ':')) {
            $host = explode(':', $host)[0];
        }

        return $host !== '' ? $host : null;
    }
}
