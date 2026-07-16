<?php

declare(strict_types=1);

namespace NenePayout\Tests\Http;

use Nene2\Auth\TokenIssuerInterface;
use NenePayout\Http\RuntimeContainerFactory;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * End-to-end proof that the opt-in X-Authorization fallback receiver (NENE2 #1558 /
 * ADR 0019) is wired into this product's runtime pipeline.
 *
 * Front-end fleet clients (`@hideyukimori/nene2-client` v1.1.0) mirror every bearer
 * token into `X-Authorization: Bearer <token>` so that shared hosting (HETEML-type
 * Tier A) — where an upstream proxy strips the standard `Authorization` header before
 * PHP sees it — can still authenticate. `RuntimeServiceProvider` enables the receiver
 * via `enableAuthorizationHeaderFallback: true`, so the framework's
 * AuthorizationHeaderFallbackMiddleware restores `Authorization` from the mirror
 * (only when `Authorization` is absent/empty) at the head of the auth stage, before
 * the bearer auth middleware runs.
 *
 * `GET /api/v1/organizations` is bearer-protected but its prefix
 * (`/api/v1/organizations`) is in `OrgResolverMiddleware::BYPASS_PREFIXES`, so it
 * skips tenant resolution entirely — these assertions isolate the
 * credential-restoration behaviour with no seeded organization.
 *
 * The tests fail if the opt-in flag is removed from RuntimeServiceProvider: a
 * mirror-only request would then never restore `Authorization` and would be
 * rejected as `missing_token`.
 */
final class AuthorizationHeaderFallbackE2ETest extends TestCase
{
    private const PROTECTED_PATH = '/api/v1/organizations';

    private RequestHandlerInterface $app;
    private TokenIssuerInterface $issuer;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new RuntimeContainerFactory(dirname(__DIR__, 2)))->create();

        $app = $container->get(RequestHandlerInterface::class);
        self::assertInstanceOf(RequestHandlerInterface::class, $app);
        $this->app = $app;

        $issuer = $container->get(TokenIssuerInterface::class);
        self::assertInstanceOf(TokenIssuerInterface::class, $issuer);
        $this->issuer = $issuer;
    }

    /**
     * The mirror end-to-end proof: a valid bearer token supplied ONLY in the
     * `X-Authorization` header (no standard `Authorization`) is restored by the
     * fallback receiver and accepted by the bearer auth stage — the request passes
     * authentication.
     *
     * The bearer middleware is the only thing that issues a `WWW-Authenticate`
     * challenge; its absence proves authentication succeeded (any further 403 here
     * is downstream authorization — the capability middleware requiring a role
     * claim — which is out of scope for the transport-level mirror proof).
     */
    public function test_valid_token_in_mirror_only_passes_authentication(): void
    {
        $token = $this->issuer->issue(['sub' => 'admin-e2e', 'exp' => time() + 3600]);

        $request = (new Psr17Factory())
            ->createServerRequest('GET', self::PROTECTED_PATH)
            ->withHeader('X-Authorization', 'Bearer ' . $token);

        $response = $this->app->handle($request);

        self::assertSame(
            '',
            $response->getHeaderLine('WWW-Authenticate'),
            'A valid token mirrored only into X-Authorization must pass the bearer auth stage (no challenge issued).',
        );
    }

    /**
     * The auth stage actually receives the mirrored credential: an INVALID token
     * in `X-Authorization` only is rejected with the verifier's malformed-token
     * detail (`BearerAuthMiddleware` reached `TokenVerifierInterface::verify()`),
     * NOT the "no token was provided" detail — which is only possible if the
     * fallback receiver restored `Authorization` from the mirror before auth ran.
     *
     * `BearerAuthMiddleware` (this product's own implementation, not the NENE2
     * standard `BearerTokenMiddleware`) always sends the same
     * `WWW-Authenticate: Bearer realm="NeNe Payout"` challenge regardless of cause,
     * so the distinguishing signal is the RFC 9457 `detail` field in the
     * `application/problem+json` body, not the challenge header.
     */
    public function test_invalid_token_in_mirror_only_reaches_bearer_stage_as_invalid_not_missing(): void
    {
        $request = (new Psr17Factory())
            ->createServerRequest('GET', self::PROTECTED_PATH)
            ->withHeader('X-Authorization', 'Bearer not-a-real-token');

        $response = $this->app->handle($request);

        self::assertSame(401, $response->getStatusCode());

        /** @var array<string, mixed> $body */
        $body = json_decode((string) $response->getBody(), true);

        // The malformed-token detail (not "No valid Bearer token was provided.", the
        // detail a missing-credential request gets) proves the verifier actually ran.
        self::assertSame(
            'Token format is invalid: expected three dot-separated segments.',
            $body['detail'] ?? null,
        );
    }

    /**
     * Baseline / control: with NO credential in either header, the auth stage
     * reports the "no token was provided" detail. This is the response a
     * mirror-only request would get if the opt-in fallback were disabled.
     */
    public function test_no_credential_yields_missing_token(): void
    {
        $request = (new Psr17Factory())->createServerRequest('GET', self::PROTECTED_PATH);

        $response = $this->app->handle($request);

        self::assertSame(401, $response->getStatusCode());

        /** @var array<string, mixed> $body */
        $body = json_decode((string) $response->getBody(), true);
        self::assertSame('No valid Bearer token was provided.', $body['detail'] ?? null);
    }

    /**
     * The standard header still wins when both are present (byte-for-byte behaviour
     * unchanged on hosting that delivers `Authorization`): a valid standard token
     * authenticates even when an invalid mirror is also sent. If the receiver wrongly
     * preferred the mirror, the bearer stage would reject the invalid token with an
     * `invalid_token` challenge; its absence proves standard-header precedence.
     */
    public function test_standard_authorization_header_takes_precedence_over_mirror(): void
    {
        $token = $this->issuer->issue(['sub' => 'admin-e2e', 'exp' => time() + 3600]);

        $request = (new Psr17Factory())
            ->createServerRequest('GET', self::PROTECTED_PATH)
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->withHeader('X-Authorization', 'Bearer not-a-real-token');

        $response = $this->app->handle($request);

        self::assertSame('', $response->getHeaderLine('WWW-Authenticate'));
    }
}
