<?php

declare(strict_types=1);

namespace NenePayout\Auth;

use Nene2\Error\ProblemDetailsResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Enforces role capabilities and organization scope on authenticated requests.
 * Runs after BearerAuthMiddleware. Unauthenticated requests (no claims) pass
 * through unchanged — authentication is enforced separately.
 */
final readonly class CapabilityMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ProblemDetailsResponseFactory $problemDetails,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $claims = $request->getAttribute('nene2.auth.claims');

        if (!is_array($claims)) {
            return $handler->handle($request);
        }

        $role = Role::tryFrom(is_string($claims['role'] ?? null) ? $claims['role'] : '');

        $required = CapabilityResolver::resolve($request->getUri()->getPath() ?: '/', $request->getMethod());

        if ($required !== null && ($role === null || !$role->hasCapability($required))) {
            return $this->problemDetails->create(
                $request,
                'forbidden',
                'Forbidden',
                403,
                'You do not have permission to perform this action.',
            );
        }

        // Organization scope: a non-superadmin token may only act within its own org.
        if ($role !== null && $role !== Role::Superadmin) {
            $resolvedOrgId = $request->getAttribute('nene2.org.id');

            if (is_string($resolvedOrgId) && $resolvedOrgId !== '') {
                $tokenOrgId = is_string($claims['org_id'] ?? null) ? $claims['org_id'] : null;

                if ($tokenOrgId !== $resolvedOrgId) {
                    return $this->problemDetails->create(
                        $request,
                        'forbidden',
                        'Forbidden',
                        403,
                        'Access to this organization is not permitted.',
                    );
                }
            }
        }

        return $handler->handle($request);
    }
}
