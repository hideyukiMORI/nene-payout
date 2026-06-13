<?php

declare(strict_types=1);

namespace NenePayout\Organization\Resolution;

use Nene2\Error\ProblemDetailsResponseFactory;
use Nene2\Http\RequestScopedHolder;
use NenePayout\Organization\OrganizationRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Resolves the current organization from the request and stores its id in a
 * shared RequestScopedHolder for downstream repositories (ADR 0018). Bypassed
 * paths (health, auth, superadmin org management, webhooks, widget) pass through
 * with the holder unset; handlers on those routes must not read it.
 */
final readonly class OrgResolverMiddleware implements MiddlewareInterface
{
    /** @var list<string> */
    private const BYPASS_PREFIXES = [
        '/health',
        '/api/v1/auth/',
        '/api/v1/organizations',
        '/api/v1/webhooks/',
        '/api/v1/widget/',
    ];

    /**
     * @param RequestScopedHolder<string> $orgId
     */
    public function __construct(
        private RequestScopedHolder $orgId,
        private OrganizationRepositoryInterface $repository,
        private ProblemDetailsResponseFactory $problemDetails,
        private OrgResolutionStrategyInterface $strategy,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();

        foreach (self::BYPASS_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return $handler->handle($request);
            }
        }

        $identifier = $this->strategy->resolve($request);

        if ($identifier === null) {
            return $this->problemDetails->create(
                $request,
                'org-not-resolved',
                'Organization Not Resolved',
                404,
                'Could not determine the organization for this request. Check the TENANT_RESOLUTION configuration.',
            );
        }

        $organization = $this->repository->findBySlug($identifier)
            ?? $this->repository->findByCustomDomain($identifier);

        if ($organization === null) {
            return $this->problemDetails->create(
                $request,
                'org-not-found',
                'Organization Not Found',
                404,
                sprintf("No organization found for '%s'.", $identifier),
            );
        }

        if (!$organization->isActive) {
            return $this->problemDetails->create(
                $request,
                'org-inactive',
                'Organization Inactive',
                403,
                'This organization is currently inactive.',
            );
        }

        $this->orgId->set((string) $organization->id);

        return $handler->handle(
            $request
                ->withAttribute('nene2.org.id', $organization->id)
                ->withAttribute('nene2.org.slug', $organization->slug),
        );
    }
}
