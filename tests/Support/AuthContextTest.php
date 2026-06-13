<?php

declare(strict_types=1);

namespace NenePayout\Tests\Support;

use NenePayout\Support\AuthContext;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class AuthContextTest extends TestCase
{
    /**
     * @param array<string, mixed>|string|null $claims
     */
    private function request(array|string|null $claims): ServerRequestInterface
    {
        $request = (new Psr17Factory())->createServerRequest('GET', 'https://example.com/api/v1/vendors');

        return $claims === null ? $request : $request->withAttribute('nene2.auth.claims', $claims);
    }

    public function test_returns_uid_when_claims_present(): void
    {
        self::assertSame('01USER0000000000000000001', AuthContext::actorUserId($this->request([
            'uid' => '01USER0000000000000000001',
            'role' => 'admin',
        ])));
    }

    public function test_returns_null_when_no_claims_attribute(): void
    {
        self::assertNull(AuthContext::actorUserId($this->request(null)));
    }

    public function test_returns_null_when_claims_not_array(): void
    {
        self::assertNull(AuthContext::actorUserId($this->request('not-an-array')));
    }

    public function test_returns_null_when_uid_missing(): void
    {
        self::assertNull(AuthContext::actorUserId($this->request(['role' => 'admin'])));
    }

    public function test_returns_null_when_uid_not_string(): void
    {
        self::assertNull(AuthContext::actorUserId($this->request(['uid' => 123])));
    }
}
