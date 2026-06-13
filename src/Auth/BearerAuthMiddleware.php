<?php

declare(strict_types=1);

namespace NenePayout\Auth;

use Nene2\Auth\TokenVerificationException;
use Nene2\Auth\TokenVerifierInterface;
use Nene2\Error\ProblemDetailsResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Authenticates Bearer tokens. Bypass paths (health, login, webhooks, widget)
 * pass through; every other path requires a valid token and fails closed (401).
 * On success the verified claims are stored on the request for downstream
 * authorization (CapabilityMiddleware).
 */
final readonly class BearerAuthMiddleware implements MiddlewareInterface
{
    /** @var list<string> */
    private const BYPASS_PREFIXES = [
        '/health',
        '/api/v1/auth/login',
        '/api/v1/webhooks/',
        '/api/v1/widget/',
    ];

    public function __construct(
        private ProblemDetailsResponseFactory $problemDetails,
        private TokenVerifierInterface $verifier,
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

        $authorization = $request->getHeaderLine('Authorization');

        if (!str_starts_with($authorization, 'Bearer ')) {
            return $this->unauthorized($request, 'No valid Bearer token was provided.');
        }

        try {
            $claims = $this->verifier->verify(substr($authorization, 7));
        } catch (TokenVerificationException $e) {
            return $this->unauthorized($request, $e->getMessage());
        }

        return $handler->handle(
            $request
                ->withAttribute('nene2.auth.credential_type', 'bearer')
                ->withAttribute('nene2.auth.claims', $claims),
        );
    }

    private function unauthorized(ServerRequestInterface $request, string $detail): ResponseInterface
    {
        return $this->problemDetails
            ->create($request, 'unauthorized', 'Unauthorized', 401, $detail)
            ->withHeader('WWW-Authenticate', 'Bearer realm="NeNe Payout"');
    }
}
