<?php

declare(strict_types=1);

namespace NenePayout\Tests\Organization\Resolution;

use Nene2\Error\ProblemDetailsResponseFactory;
use Nene2\Http\RequestScopedHolder;
use NenePayout\Organization\Organization;
use NenePayout\Organization\Resolution\EnvResolutionStrategy;
use NenePayout\Organization\Resolution\OrgResolverMiddleware;
use NenePayout\Tests\Organization\InMemoryOrganizationRepository;
use NenePayout\Tests\Support\CapturingRequestHandler;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class OrgResolverMiddlewareTest extends TestCase
{
    private Psr17Factory $psr17;
    private ProblemDetailsResponseFactory $problemDetails;

    protected function setUp(): void
    {
        $this->psr17 = new Psr17Factory();
        $this->problemDetails = new ProblemDetailsResponseFactory(
            $this->psr17,
            $this->psr17,
            'https://nene-payout.dev/problems/',
        );
    }

    private function handler(): CapturingRequestHandler
    {
        return new CapturingRequestHandler($this->psr17);
    }

    public function test_resolves_active_org_and_populates_holder(): void
    {
        $org = new Organization(slug: 'payout', name: 'Example Co.', isActive: true, id: '01ORG00000000000000000001');
        /** @var RequestScopedHolder<string> $holder */
        $holder = new RequestScopedHolder();
        $middleware = new OrgResolverMiddleware($holder, new InMemoryOrganizationRepository($org), $this->problemDetails, new EnvResolutionStrategy('payout'));
        $handler = $this->handler();

        $response = $middleware->process(
            $this->psr17->createServerRequest('GET', 'https://example.com/api/v1/vendors'),
            $handler,
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('01ORG00000000000000000001', $holder->get());
        self::assertInstanceOf(ServerRequestInterface::class, $handler->seen);
        self::assertSame('01ORG00000000000000000001', $handler->seen->getAttribute('nene2.org.id'));
        self::assertSame('payout', $handler->seen->getAttribute('nene2.org.slug'));
    }

    public function test_bypasses_health_without_setting_holder(): void
    {
        /** @var RequestScopedHolder<string> $holder */
        $holder = new RequestScopedHolder();
        $middleware = new OrgResolverMiddleware($holder, new InMemoryOrganizationRepository(), $this->problemDetails, new EnvResolutionStrategy('payout'));

        $response = $middleware->process(
            $this->psr17->createServerRequest('GET', 'https://example.com/health'),
            $this->handler(),
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertFalse($holder->isSet());
    }

    public function test_returns_404_when_org_cannot_be_resolved(): void
    {
        /** @var RequestScopedHolder<string> $holder */
        $holder = new RequestScopedHolder();
        $middleware = new OrgResolverMiddleware($holder, new InMemoryOrganizationRepository(), $this->problemDetails, new EnvResolutionStrategy(''));
        $handler = $this->handler();

        $response = $middleware->process(
            $this->psr17->createServerRequest('GET', 'https://example.com/api/v1/vendors'),
            $handler,
        );

        self::assertSame(404, $response->getStatusCode());
        self::assertNull($handler->seen);
    }

    public function test_returns_404_when_org_not_found(): void
    {
        /** @var RequestScopedHolder<string> $holder */
        $holder = new RequestScopedHolder();
        $middleware = new OrgResolverMiddleware($holder, new InMemoryOrganizationRepository(), $this->problemDetails, new EnvResolutionStrategy('missing'));

        $response = $middleware->process(
            $this->psr17->createServerRequest('GET', 'https://example.com/api/v1/vendors'),
            $this->handler(),
        );

        self::assertSame(404, $response->getStatusCode());
    }

    public function test_returns_403_when_org_inactive(): void
    {
        $org = new Organization(slug: 'payout', name: 'Example Co.', isActive: false, id: '01ORG00000000000000000001');
        /** @var RequestScopedHolder<string> $holder */
        $holder = new RequestScopedHolder();
        $middleware = new OrgResolverMiddleware($holder, new InMemoryOrganizationRepository($org), $this->problemDetails, new EnvResolutionStrategy('payout'));

        $response = $middleware->process(
            $this->psr17->createServerRequest('GET', 'https://example.com/api/v1/vendors'),
            $this->handler(),
        );

        self::assertSame(403, $response->getStatusCode());
    }
}
