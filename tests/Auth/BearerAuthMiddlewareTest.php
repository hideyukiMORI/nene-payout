<?php

declare(strict_types=1);

namespace NenePayout\Tests\Auth;

use Nene2\Auth\LocalBearerTokenVerifier;
use Nene2\Error\ProblemDetailsResponseFactory;
use NenePayout\Auth\BearerAuthMiddleware;
use NenePayout\Tests\Support\CapturingRequestHandler;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class BearerAuthMiddlewareTest extends TestCase
{
    private Psr17Factory $psr17;
    private LocalBearerTokenVerifier $verifier;
    private BearerAuthMiddleware $middleware;

    protected function setUp(): void
    {
        $this->psr17 = new Psr17Factory();
        $this->verifier = new LocalBearerTokenVerifier('test-secret');
        $problemDetails = new ProblemDetailsResponseFactory($this->psr17, $this->psr17, 'https://nene-payout.dev/problems/');
        $this->middleware = new BearerAuthMiddleware($problemDetails, $this->verifier);
    }

    public function test_login_path_bypasses_authentication(): void
    {
        $handler = new CapturingRequestHandler($this->psr17);

        $response = $this->middleware->process(
            $this->psr17->createServerRequest('POST', 'https://example.com/api/v1/auth/login'),
            $handler,
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertNotNull($handler->seen);
    }

    public function test_protected_path_without_token_returns_401(): void
    {
        $handler = new CapturingRequestHandler($this->psr17);

        $response = $this->middleware->process(
            $this->psr17->createServerRequest('GET', 'https://example.com/api/v1/vendors'),
            $handler,
        );

        self::assertSame(401, $response->getStatusCode());
        self::assertNull($handler->seen);
    }

    public function test_valid_token_populates_claims_and_continues(): void
    {
        $token = $this->verifier->issue([
            'sub' => 'admin@example.com',
            'role' => 'admin',
            'org_id' => '01ORG00000000000000000001',
            'exp' => time() + 3600,
        ]);
        $handler = new CapturingRequestHandler($this->psr17);

        $request = $this->psr17->createServerRequest('GET', 'https://example.com/api/v1/vendors')
            ->withHeader('Authorization', 'Bearer ' . $token);

        $response = $this->middleware->process($request, $handler);

        self::assertSame(200, $response->getStatusCode());
        self::assertNotNull($handler->seen);
        $claims = $handler->seen->getAttribute('nene2.auth.claims');
        self::assertIsArray($claims);
        self::assertSame('admin', $claims['role']);
    }
}
