<?php

declare(strict_types=1);

namespace NenePayout\Tests\Auth;

use Nene2\Error\ProblemDetailsResponseFactory;
use NenePayout\Auth\CapabilityMiddleware;
use NenePayout\Tests\Support\CapturingRequestHandler;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class CapabilityMiddlewareTest extends TestCase
{
    private Psr17Factory $psr17;
    private CapabilityMiddleware $middleware;

    protected function setUp(): void
    {
        $this->psr17 = new Psr17Factory();
        $problemDetails = new ProblemDetailsResponseFactory($this->psr17, $this->psr17, 'https://nene-payout.dev/problems/');
        $this->middleware = new CapabilityMiddleware($problemDetails);
    }

    /** @param array<string, mixed> $claims */
    private function request(string $method, string $path, array $claims, ?string $resolvedOrgId = null): ServerRequestInterface
    {
        $request = $this->psr17->createServerRequest($method, 'https://example.com' . $path)
            ->withAttribute('nene2.auth.claims', $claims);

        if ($resolvedOrgId !== null) {
            $request = $request->withAttribute('nene2.org.id', $resolvedOrgId);
        }

        return $request;
    }

    public function test_operator_blocked_from_managing_vendors(): void
    {
        $handler = new CapturingRequestHandler($this->psr17);

        $response = $this->middleware->process(
            $this->request('POST', '/api/v1/vendors', ['role' => 'operator', 'org_id' => '01ORG00000000000000000001']),
            $handler,
        );

        self::assertSame(403, $response->getStatusCode());
        self::assertNull($handler->seen);
    }

    public function test_admin_allowed_to_manage_vendors(): void
    {
        $handler = new CapturingRequestHandler($this->psr17);

        $response = $this->middleware->process(
            $this->request('POST', '/api/v1/vendors', ['role' => 'admin', 'org_id' => '01ORG00000000000000000001']),
            $handler,
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertNotNull($handler->seen);
    }

    public function test_cross_org_access_is_forbidden(): void
    {
        $handler = new CapturingRequestHandler($this->psr17);

        $response = $this->middleware->process(
            $this->request('GET', '/api/v1/payment-executions', ['role' => 'admin', 'org_id' => '01ORG_A'], '01ORG_B'),
            $handler,
        );

        self::assertSame(403, $response->getStatusCode());
    }

    public function test_unauthenticated_request_passes_through(): void
    {
        $handler = new CapturingRequestHandler($this->psr17);

        $response = $this->middleware->process(
            $this->psr17->createServerRequest('GET', 'https://example.com/api/v1/vendors'),
            $handler,
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertNotNull($handler->seen);
    }
}
