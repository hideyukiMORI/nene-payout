<?php

declare(strict_types=1);

namespace NenePayout\Tests\Auth;

use Nene2\Auth\LocalBearerTokenVerifier;
use Nene2\Http\JsonResponseFactory;
use Nene2\Validation\ValidationException;
use NenePayout\Auth\InvalidCredentialsException;
use NenePayout\Auth\LoginHandler;
use NenePayout\Auth\LoginUseCase;
use NenePayout\Auth\User;
use NenePayout\Tests\Support\FixedClock;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class LoginHandlerTest extends TestCase
{
    private Psr17Factory $psr17;

    protected function setUp(): void
    {
        $this->psr17 = new Psr17Factory();
    }

    /**
     * @param array<string, mixed> $body
     */
    private function request(array $body): ServerRequestInterface
    {
        return $this->psr17->createServerRequest('POST', 'https://example.com/api/v1/auth/login')
            ->withBody($this->psr17->createStream((string) json_encode($body)));
    }

    private function handler(): LoginHandler
    {
        $user = new User(
            id: '01USER0000000000000000001',
            email: 'admin@example.com',
            passwordHash: password_hash('correct horse', PASSWORD_DEFAULT),
            role: 'admin',
            organizationId: '01ORG00000000000000000001',
        );

        $useCase = new LoginUseCase(
            new InMemoryUserRepository($user),
            new LocalBearerTokenVerifier('test-secret'),
            new FixedClock(),
        );

        return new LoginHandler($useCase, new JsonResponseFactory($this->psr17, $this->psr17));
    }

    public function test_valid_credentials_return_200_with_token(): void
    {
        $response = $this->handler()->handle($this->request(['email' => 'admin@example.com', 'password' => 'correct horse']));

        self::assertSame(200, $response->getStatusCode());

        /** @var array<string, mixed> $body */
        $body = json_decode((string) $response->getBody(), true);
        self::assertIsString($body['token']);
        self::assertSame('admin@example.com', $body['email']);
        self::assertSame('admin', $body['role']);
        self::assertSame('01ORG00000000000000000001', $body['org_id']);
    }

    public function test_missing_email_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $this->handler()->handle($this->request(['password' => 'x']));
    }

    public function test_missing_password_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $this->handler()->handle($this->request(['email' => 'admin@example.com']));
    }

    public function test_wrong_password_raises_invalid_credentials(): void
    {
        $this->expectException(InvalidCredentialsException::class);
        $this->handler()->handle($this->request(['email' => 'admin@example.com', 'password' => 'nope']));
    }
}
