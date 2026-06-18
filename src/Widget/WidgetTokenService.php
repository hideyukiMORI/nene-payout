<?php

declare(strict_types=1);

namespace NenePayout\Widget;

use Nene2\Auth\TokenIssuerInterface;
use Nene2\Auth\TokenVerificationException;
use Nene2\Auth\TokenVerifierInterface;
use Nene2\Http\ClockInterface;

/**
 * Issues and verifies organization-scoped widget tokens (ADR 0021). Signed with
 * the same HS256 mechanism as user bearer tokens, but carries `scope: 'widget'`
 * and an `org_id` instead of a user identity; verification rejects any token
 * whose scope is not `widget`, so a widget token cannot act as a user bearer and
 * vice versa.
 */
final readonly class WidgetTokenService
{
    private const SCOPE = 'widget';

    public function __construct(
        private TokenIssuerInterface $issuer,
        private TokenVerifierInterface $verifier,
        private ClockInterface $clock,
        private int $ttlSeconds,
    ) {
    }

    /**
     * @return array{token: string, expiresAt: int}
     */
    public function issue(string $organizationId): array
    {
        $now = $this->clock->now()->getTimestamp();
        $expiresAt = $now + $this->ttlSeconds;

        $token = $this->issuer->issue([
            'scope'  => self::SCOPE,
            'org_id' => $organizationId,
            'iat'    => $now,
            'exp'    => $expiresAt,
        ]);

        return ['token' => $token, 'expiresAt' => $expiresAt];
    }

    /**
     * @throws WidgetTokenException
     */
    public function verify(string $token): WidgetToken
    {
        try {
            $claims = $this->verifier->verify($token);
        } catch (TokenVerificationException $e) {
            throw new WidgetTokenException($e->getMessage(), previous: $e);
        }

        if (($claims['scope'] ?? null) !== self::SCOPE) {
            throw new WidgetTokenException('Token scope is not a widget token.');
        }

        $organizationId = $claims['org_id'] ?? null;

        if (!is_string($organizationId) || $organizationId === '') {
            throw new WidgetTokenException('Token is missing a valid org_id claim.');
        }

        return new WidgetToken($organizationId);
    }
}
