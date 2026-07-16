<?php

declare(strict_types=1);

namespace NenePayout\Tests\Widget;

use DateTimeImmutable;
use Nene2\Auth\LocalBearerTokenVerifier;
use Nene2\Error\ProblemDetailsResponseFactory;
use Nene2\Http\ClockInterface;
use Nene2\Http\RequestScopedHolder;
use NenePayout\Tests\Support\CapturingRequestHandler;
use NenePayout\Widget\WidgetAuthMiddleware;
use NenePayout\Widget\WidgetTokenService;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class WidgetAuthMiddlewareTest extends TestCase
{
    private const ORG = '01ORG00000000000000000007';

    private Psr17Factory $psr17;
    private WidgetTokenService $tokens;
    /** @var RequestScopedHolder<string> */
    private RequestScopedHolder $orgId;
    private WidgetAuthMiddleware $middleware;

    protected function setUp(): void
    {
        $this->psr17 = new Psr17Factory();
        $verifier = new LocalBearerTokenVerifier('secret');
        $clock = new class () implements ClockInterface {
            public function now(): DateTimeImmutable
            {
                return new DateTimeImmutable();
            }
        };
        $this->tokens = new WidgetTokenService($verifier, $verifier, $clock, 3600);
        $this->orgId = new RequestScopedHolder();
        $problemDetails = new ProblemDetailsResponseFactory($this->psr17, $this->psr17, 'https://nene-payout.dev/problems/');
        $this->middleware = new WidgetAuthMiddleware($this->tokens, $this->orgId, $problemDetails);
    }

    public function test_non_widget_path_passes_through_untouched(): void
    {
        $handler = new CapturingRequestHandler($this->psr17);

        $response = $this->middleware->process(
            $this->psr17->createServerRequest('GET', 'https://example.com/api/v1/vendors'),
            $handler,
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertNotNull($handler->seen);
    }

    public function test_widget_path_without_token_returns_401(): void
    {
        $handler = new CapturingRequestHandler($this->psr17);

        $response = $this->middleware->process(
            $this->psr17->createServerRequest('GET', 'https://example.com/api/v1/widget/context'),
            $handler,
        );

        self::assertSame(401, $response->getStatusCode());
        self::assertNull($handler->seen);
    }

    public function test_widget_path_with_invalid_token_returns_401(): void
    {
        $handler = new CapturingRequestHandler($this->psr17);
        $request = $this->psr17->createServerRequest('GET', 'https://example.com/api/v1/widget/context')
            ->withHeader('X-Widget-Token', 'not-a-valid-token');

        $response = $this->middleware->process($request, $handler);

        self::assertSame(401, $response->getStatusCode());
        self::assertNull($handler->seen);
    }

    public function test_valid_token_sets_org_holder_and_continues(): void
    {
        $issued = $this->tokens->issue(self::ORG);
        $handler = new CapturingRequestHandler($this->psr17);
        $request = $this->psr17->createServerRequest('GET', 'https://example.com/api/v1/widget/received-invoices')
            ->withHeader('X-Widget-Token', $issued['token']);

        $response = $this->middleware->process($request, $handler);

        self::assertSame(200, $response->getStatusCode());
        self::assertNotNull($handler->seen);
        self::assertSame(self::ORG, $this->orgId->get());
        self::assertSame(self::ORG, $handler->seen->getAttribute('nene2.org.id'));
    }
}
