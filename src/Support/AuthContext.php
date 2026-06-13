<?php

declare(strict_types=1);

namespace NenePayout\Support;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Reads the authenticated actor from the verified token claims that
 * BearerAuthMiddleware placed on the request.
 */
final class AuthContext
{
    public static function actorUserId(ServerRequestInterface $request): ?string
    {
        $claims = $request->getAttribute('nene2.auth.claims');

        if (is_array($claims) && isset($claims['uid']) && is_string($claims['uid'])) {
            return $claims['uid'];
        }

        return null;
    }
}
