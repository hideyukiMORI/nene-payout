<?php

declare(strict_types=1);

namespace NenePayout\Widget;

use Nene2\Error\ProblemDetailsResponseFactory;
use Nene2\Http\RequestScopedHolder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Authenticates the embeddable widget on `/api/v1/widget/*` only. Validates the
 * `X-Widget-Token` header, derives the organization from the signed token, and
 * stores it in the shared org-id holder so tenant-scoped repositories filter by
 * it (ADR 0018, 0021) — the user Bearer / OrgResolver middleware already bypass
 * this prefix. Every other path passes through untouched. Missing/invalid tokens
 * fail closed (401).
 */
final readonly class WidgetAuthMiddleware implements MiddlewareInterface
{
    private const PREFIX = '/api/v1/widget/';

    /**
     * @param RequestScopedHolder<string> $orgId
     */
    public function __construct(
        private WidgetTokenService $tokens,
        private RequestScopedHolder $orgId,
        private ProblemDetailsResponseFactory $problemDetails,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();

        if (!str_starts_with($path, self::PREFIX)) {
            return $handler->handle($request);
        }

        $token = $request->getHeaderLine('X-Widget-Token');

        if ($token === '') {
            return $this->unauthorized($request, 'A widget token is required.');
        }

        try {
            $widgetToken = $this->tokens->verify($token);
        } catch (WidgetTokenException $e) {
            return $this->unauthorized($request, $e->getMessage());
        }

        $this->orgId->set($widgetToken->organizationId);

        return $handler->handle(
            $request->withAttribute('nene2.org.id', $widgetToken->organizationId),
        );
    }

    private function unauthorized(ServerRequestInterface $request, string $detail): ResponseInterface
    {
        return $this->problemDetails->create($request, 'unauthorized', 'Unauthorized', 401, $detail);
    }
}
