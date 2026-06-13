<?php

declare(strict_types=1);

namespace NenePayout\Tests\Auth;

use Nene2\Auth\LocalBearerTokenVerifier;
use Nene2\Http\UtcClock;
use NenePayout\Auth\InvalidCredentialsException;
use NenePayout\Auth\LoginInput;
use NenePayout\Auth\LoginUseCase;
use NenePayout\Auth\User;
use PHPUnit\Framework\TestCase;

final class LoginUseCaseTest extends TestCase
{
    private function useCase(User ...$users): LoginUseCase
    {
        return new LoginUseCase(
            new InMemoryUserRepository(...$users),
            new LocalBearerTokenVerifier('test-secret'),
            new UtcClock(),
        );
    }

    private function user(string $password = 'correct horse'): User
    {
        return new User(
            id: '01USER0000000000000000001',
            email: 'admin@example.com',
            passwordHash: password_hash($password, PASSWORD_DEFAULT),
            role: 'admin',
            organizationId: '01ORG00000000000000000001',
        );
    }

    public function test_issues_a_verifiable_token_on_valid_credentials(): void
    {
        $useCase = $this->useCase($this->user());

        $output = $useCase->execute(new LoginInput('admin@example.com', 'correct horse'));

        self::assertSame('admin@example.com', $output->email);
        self::assertSame('admin', $output->role);
        self::assertSame('01ORG00000000000000000001', $output->organizationId);

        $claims = (new LocalBearerTokenVerifier('test-secret'))->verify($output->token);
        self::assertSame('admin@example.com', $claims['sub']);
        self::assertSame('01USER0000000000000000001', $claims['uid']);
        self::assertSame('admin', $claims['role']);
        self::assertSame('01ORG00000000000000000001', $claims['org_id']);
    }

    public function test_rejects_wrong_password(): void
    {
        $useCase = $this->useCase($this->user());

        $this->expectException(InvalidCredentialsException::class);
        $useCase->execute(new LoginInput('admin@example.com', 'wrong'));
    }

    public function test_rejects_unknown_email(): void
    {
        $useCase = $this->useCase($this->user());

        $this->expectException(InvalidCredentialsException::class);
        $useCase->execute(new LoginInput('nobody@example.com', 'correct horse'));
    }

    public function test_superadmin_token_has_null_org(): void
    {
        $superadmin = new User(
            id: '01USER0000000000000000002',
            email: 'root@example.com',
            passwordHash: password_hash('secret', PASSWORD_DEFAULT),
            role: 'superadmin',
            organizationId: null,
        );

        $output = $this->useCase($superadmin)->execute(new LoginInput('root@example.com', 'secret'));

        self::assertNull($output->organizationId);
    }
}
