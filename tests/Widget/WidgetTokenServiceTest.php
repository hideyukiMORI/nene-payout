<?php

declare(strict_types=1);

namespace NenePayout\Tests\Widget;

use DateTimeImmutable;
use Nene2\Auth\LocalBearerTokenVerifier;
use Nene2\Http\ClockInterface;
use NenePayout\Widget\WidgetToken;
use NenePayout\Widget\WidgetTokenException;
use NenePayout\Widget\WidgetTokenService;
use PHPUnit\Framework\TestCase;

final class WidgetTokenServiceTest extends TestCase
{
    private const ORG = '01ORG00000000000000000001';

    public function test_issue_then_verify_roundtrips_the_org(): void
    {
        $verifier = new LocalBearerTokenVerifier('secret');
        // Issue at the current time so the verifier's real-clock exp check passes.
        $now = time();
        $service = new WidgetTokenService($verifier, $verifier, $this->clock($now), 3600);

        $issued = $service->issue(self::ORG);

        self::assertSame($now + 3600, $issued['expiresAt']);

        $token = $service->verify($issued['token']);

        self::assertInstanceOf(WidgetToken::class, $token);
        self::assertSame(self::ORG, $token->organizationId);
    }

    public function test_rejects_a_non_widget_scoped_token(): void
    {
        $verifier = new LocalBearerTokenVerifier('secret');
        // A user-style bearer token must not pass as a widget token.
        $userToken = $verifier->issue(['scope' => 'user', 'org_id' => self::ORG, 'exp' => time() + 3600]);
        $service = new WidgetTokenService($verifier, $verifier, $this->clock(time()), 3600);

        $this->expectException(WidgetTokenException::class);
        $service->verify($userToken);
    }

    public function test_rejects_an_expired_token(): void
    {
        $verifier = new LocalBearerTokenVerifier('secret');
        // Clock in the past so exp = (past + ttl) is still before real now().
        $service = new WidgetTokenService($verifier, $verifier, $this->clock(time() - 10_000), 3600);
        $issued = $service->issue(self::ORG);

        $this->expectException(WidgetTokenException::class);
        $service->verify($issued['token']);
    }

    public function test_rejects_a_tampered_token(): void
    {
        $verifier = new LocalBearerTokenVerifier('secret');
        $service = new WidgetTokenService($verifier, $verifier, $this->clock(time()), 3600);
        $issued = $service->issue(self::ORG);

        $this->expectException(WidgetTokenException::class);
        $service->verify($issued['token'] . 'tampered');
    }

    public function test_rejects_a_token_signed_with_a_different_secret(): void
    {
        $issuerSide = new LocalBearerTokenVerifier('secret-a');
        $verifierSide = new LocalBearerTokenVerifier('secret-b');
        $issuer = new WidgetTokenService($issuerSide, $issuerSide, $this->clock(time()), 3600);
        $verifier = new WidgetTokenService($verifierSide, $verifierSide, $this->clock(time()), 3600);
        $issued = $issuer->issue(self::ORG);

        $this->expectException(WidgetTokenException::class);
        $verifier->verify($issued['token']);
    }

    private function clock(int $timestamp): ClockInterface
    {
        return new class ($timestamp) implements ClockInterface {
            public function __construct(private readonly int $timestamp)
            {
            }

            public function now(): DateTimeImmutable
            {
                return (new DateTimeImmutable())->setTimestamp($this->timestamp);
            }
        };
    }
}
