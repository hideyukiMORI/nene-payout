<?php

declare(strict_types=1);

namespace NenePayout\Tests\User;

use NenePayout\Auth\User;
use NenePayout\User\GetUserUseCase;
use NenePayout\User\ListUsersUseCase;
use NenePayout\User\UserNotFoundException;
use PHPUnit\Framework\TestCase;

final class UserQueryUseCaseTest extends TestCase
{
    private function user(string $id, string $email, string $status = 'active'): User
    {
        return new User(
            id: $id,
            email: $email,
            passwordHash: '',
            role: 'operator',
            organizationId: '01ORG00000000000000000001',
            status: $status,
        );
    }

    public function test_list_empty_repository_returns_zero(): void
    {
        $out = (new ListUsersUseCase(new InMemoryUserRepository()))->execute(20, 0);

        self::assertSame(0, $out->total);
        self::assertSame([], $out->items);
    }

    public function test_list_excludes_deactivated_from_total_and_items(): void
    {
        $repo = new InMemoryUserRepository(
            $this->user('01A', 'a@example.com'),
            $this->user('01B', 'b@example.com', 'deactivated'),
            $this->user('01C', 'c@example.com', 'invited'),
        );

        $out = (new ListUsersUseCase($repo))->execute(20, 0);

        self::assertSame(2, $out->total);
        self::assertCount(2, $out->items);
    }

    public function test_list_pagination_boundaries(): void
    {
        $repo = new InMemoryUserRepository(
            $this->user('01A', 'a@example.com'),
            $this->user('01B', 'b@example.com'),
            $this->user('01C', 'c@example.com'),
        );
        $useCase = new ListUsersUseCase($repo);

        self::assertSame(3, $useCase->execute(2, 0)->total);
        self::assertCount(2, $useCase->execute(2, 0)->items);
        self::assertCount(1, $useCase->execute(2, 2)->items);
        self::assertCount(0, $useCase->execute(2, 5)->items);
    }

    public function test_get_returns_user_when_present(): void
    {
        $repo = new InMemoryUserRepository($this->user('01A', 'a@example.com'));

        self::assertSame('a@example.com', (new GetUserUseCase($repo))->execute('01A')->email);
    }

    public function test_get_throws_for_missing_or_deactivated_user(): void
    {
        $repo = new InMemoryUserRepository($this->user('01B', 'b@example.com', 'deactivated'));
        $useCase = new GetUserUseCase($repo);

        $this->expectException(UserNotFoundException::class);
        $useCase->execute('01B'); // deactivated surfaces as not found
    }
}
