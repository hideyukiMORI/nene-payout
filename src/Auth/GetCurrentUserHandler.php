<?php

declare(strict_types=1);

namespace NenePayout\Auth;

use Nene2\Http\JsonResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Returns the authenticated user from the verified token claims (GET /auth/me).
 * Reaches this handler only after BearerAuthMiddleware has populated claims.
 */
final readonly class GetCurrentUserHandler
{
    public function __construct(
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $claims = $request->getAttribute('nene2.auth.claims');
        $claims = is_array($claims) ? $claims : [];

        return $this->response->create([
            'id'              => is_string($claims['uid'] ?? null) ? $claims['uid'] : null,
            'email'           => is_string($claims['sub'] ?? null) ? $claims['sub'] : null,
            'role'            => is_string($claims['role'] ?? null) ? $claims['role'] : null,
            'organization_id' => is_string($claims['org_id'] ?? null) ? $claims['org_id'] : null,
        ]);
    }
}
