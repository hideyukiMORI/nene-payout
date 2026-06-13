<?php

declare(strict_types=1);

namespace NenePayout\Tests\Organization\Resolution;

use NenePayout\Organization\Resolution\EnvResolutionStrategy;
use NenePayout\Organization\Resolution\PathPrefixResolutionStrategy;
use NenePayout\Organization\Resolution\SubdomainResolutionStrategy;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class ResolutionStrategyTest extends TestCase
{
    private function request(string $uri): \Psr\Http\Message\ServerRequestInterface
    {
        return (new Psr17Factory())->createServerRequest('GET', $uri);
    }

    public function test_env_strategy_returns_configured_slug(): void
    {
        $strategy = new EnvResolutionStrategy('payout');
        self::assertSame('payout', $strategy->resolve($this->request('https://example.com/api/v1/vendors')));
    }

    public function test_env_strategy_returns_null_when_empty(): void
    {
        $strategy = new EnvResolutionStrategy('');
        self::assertNull($strategy->resolve($this->request('https://example.com/api/v1/vendors')));
    }

    public function test_subdomain_strategy_extracts_subdomain(): void
    {
        $strategy = new SubdomainResolutionStrategy('pay.example.com');
        self::assertSame('acme', $strategy->resolve($this->request('https://acme.pay.example.com/api/v1/vendors')));
    }

    public function test_subdomain_strategy_returns_null_on_bare_base_domain(): void
    {
        $strategy = new SubdomainResolutionStrategy('pay.example.com');
        self::assertNull($strategy->resolve($this->request('https://pay.example.com/api/v1/vendors')));
    }

    public function test_path_strategy_extracts_first_segment(): void
    {
        $strategy = new PathPrefixResolutionStrategy();
        self::assertSame('acme', $strategy->resolve($this->request('https://example.com/acme/api/v1/vendors')));
    }

    public function test_path_strategy_bypasses_infrastructure_paths(): void
    {
        $strategy = new PathPrefixResolutionStrategy();
        self::assertNull($strategy->resolve($this->request('https://example.com/health')));
        self::assertNull($strategy->resolve($this->request('https://example.com/api/v1/auth/login')));
    }
}
