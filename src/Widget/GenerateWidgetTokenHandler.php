<?php

declare(strict_types=1);

namespace NenePayout\Widget;

use DateTimeImmutable;
use DateTimeInterface;
use LogicException;
use Nene2\Http\JsonResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Issues an organization-scoped widget token and the ready-to-paste embed
 * snippet. This route (`POST /api/v1/widget-tokens`) is protected by the normal
 * auth + org + capability pipeline (ManageOrganizationSettings), so the org comes
 * from the resolved tenant on the request, not from a widget token.
 */
final readonly class GenerateWidgetTokenHandler
{
    public function __construct(
        private WidgetTokenService $tokens,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $organizationId = $request->getAttribute('nene2.org.id');

        if (!is_string($organizationId) || $organizationId === '') {
            throw new LogicException('Organization was not resolved for widget token generation.');
        }

        $issued = $this->tokens->issue($organizationId);
        $snippet = $this->embedSnippet(self::baseUrl($request), $issued['token']);

        return $this->response->create([
            'token'         => $issued['token'],
            'expires_at'    => (new DateTimeImmutable('@' . $issued['expiresAt']))->format(DateTimeInterface::ATOM),
            'embed_snippet' => $snippet,
        ], 201);
    }

    private function embedSnippet(string $baseUrl, string $token): string
    {
        return sprintf(
            '<script src="%s/assets/widget.js" data-payout-token="%s" data-payout-mode="modal" async></script>',
            $baseUrl,
            $token,
        );
    }

    /**
     * Origin to serve `widget.js` from: an explicit `WIDGET_PUBLIC_BASE_URL`
     * (for reverse-proxy setups), else the scheme+host of the generating request.
     */
    private static function baseUrl(ServerRequestInterface $request): string
    {
        $env = getenv('WIDGET_PUBLIC_BASE_URL');
        if (is_string($env) && $env !== '') {
            return rtrim($env, '/');
        }

        $uri = $request->getUri();
        $base = $uri->getScheme() . '://' . $uri->getHost();
        $port = $uri->getPort();

        if ($port !== null && !in_array($port, [80, 443], true)) {
            $base .= ':' . $port;
        }

        return $base;
    }
}
