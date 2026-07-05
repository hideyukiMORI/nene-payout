<?php

declare(strict_types=1);

namespace NenePayout\Tests\User;

use Closure;
use Nene2\Audit\AuditRecorderFactoryInterface;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Http\RequestScopedHolder;
use NenePayout\Auth\User;
use NenePayout\Tests\Audit\InMemoryAuditRecorderFactory;
use NenePayout\Tests\Support\FixedClock;
use NenePayout\Tests\Support\ImmediateTransactionManager;
use NenePayout\User\CreateUserInput;
use NenePayout\User\CreateUserUseCase;
use NenePayout\User\DeactivateUserUseCase;
use NenePayout\User\UpdateUserInput;
use NenePayout\User\UpdateUserUseCase;
use NenePayout\User\UserEmailConflictException;
use NenePayout\User\UserNotFoundException;
use NenePayout\User\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class UserUseCaseTest extends TestCase
{
    private InMemoryAuditRecorderFactory $auditRepo;

    /** @var RequestScopedHolder<string> */
    private RequestScopedHolder $orgId;

    protected function setUp(): void
    {
        $this->auditRepo = new InMemoryAuditRecorderFactory(new FixedClock());
        /** @var RequestScopedHolder<string> $holder */
        $holder = new RequestScopedHolder();
        $holder->set('01ORG00000000000000000001');
        $this->orgId = $holder;
    }

    /** @return Closure(DatabaseQueryExecutorInterface): UserRepositoryInterface */
    private function usersFactory(UserRepositoryInterface $repo): Closure
    {
        return static fn (DatabaseQueryExecutorInterface $exec): UserRepositoryInterface => $repo;
    }

    private function auditFactory(): AuditRecorderFactoryInterface
    {
        return $this->auditRepo;
    }

    private function existing(string $id, string $email, string $role = 'operator', string $status = 'active'): User
    {
        return new User(
            id: $id,
            email: $email,
            passwordHash: 'hash',
            role: $role,
            organizationId: '01ORG00000000000000000001',
            status: $status,
        );
    }

    public function test_create_invites_user_and_records_audit(): void
    {
        $repo = new InMemoryUserRepository();
        $useCase = new CreateUserUseCase(
            new ImmediateTransactionManager(),
            $this->usersFactory($repo),
            $this->auditFactory(),
            $this->orgId,
        );

        $user = $useCase->execute('01USER0000000000000000001', new CreateUserInput('new@example.com', 'operator'));

        self::assertNotSame('', $user->id);
        self::assertSame('new@example.com', $user->email);
        self::assertSame('operator', $user->role);
        self::assertSame('invited', $user->status);
        self::assertSame('', $user->passwordHash);
        self::assertSame('01ORG00000000000000000001', $user->organizationId);

        self::assertCount(1, $this->auditRepo->appended);
        $log = $this->auditRepo->appended[0];
        self::assertSame('user.created', $log->action);
        self::assertNull($log->before);
        self::assertSame('new@example.com', $log->after['email'] ?? null);
        // No credential material in audit snapshots.
        self::assertArrayNotHasKey('password_hash', (array) $log->after);
    }

    public function test_create_rejects_duplicate_email_with_conflict(): void
    {
        $repo = new InMemoryUserRepository($this->existing('01A', 'dupe@example.com'));
        $useCase = new CreateUserUseCase(
            new ImmediateTransactionManager(),
            $this->usersFactory($repo),
            $this->auditFactory(),
            $this->orgId,
        );

        $this->expectException(UserEmailConflictException::class);
        $useCase->execute(null, new CreateUserInput('dupe@example.com', 'admin'));
    }

    public function test_update_changes_role_and_records_before_after(): void
    {
        $repo = new InMemoryUserRepository($this->existing('01A', 'user@example.com', 'operator'));
        $useCase = new UpdateUserUseCase(
            $repo,
            new ImmediateTransactionManager(),
            $this->usersFactory($repo),
            $this->auditFactory(),
            $this->orgId,
        );

        $updated = $useCase->execute('01USER0000000000000000001', '01A', new UpdateUserInput('admin'));

        self::assertSame('admin', $updated->role);
        self::assertSame('user@example.com', $updated->email);
        self::assertSame('hash', $updated->passwordHash); // preserved

        $log = $this->auditRepo->appended[0];
        self::assertSame('user.updated', $log->action);
        self::assertSame('operator', $log->before['role'] ?? null);
        self::assertSame('admin', $log->after['role'] ?? null);
    }

    public function test_update_unknown_user_throws(): void
    {
        $repo = new InMemoryUserRepository();
        $useCase = new UpdateUserUseCase(
            $repo,
            new ImmediateTransactionManager(),
            $this->usersFactory($repo),
            $this->auditFactory(),
            $this->orgId,
        );

        $this->expectException(UserNotFoundException::class);
        $useCase->execute(null, 'missing', new UpdateUserInput('admin'));
    }

    public function test_deactivate_soft_deletes_and_records_audit(): void
    {
        $repo = new InMemoryUserRepository($this->existing('01A', 'user@example.com'));
        $useCase = new DeactivateUserUseCase(
            $repo,
            new ImmediateTransactionManager(),
            $this->usersFactory($repo),
            $this->auditFactory(),
            $this->orgId,
        );

        $deactivated = $useCase->execute('01USER0000000000000000001', '01A');

        self::assertSame('deactivated', $deactivated->status);
        self::assertNull($repo->findById('01A'));

        $log = $this->auditRepo->appended[0];
        self::assertSame('user.deactivated', $log->action);
        self::assertSame('user@example.com', $log->before['email'] ?? null);
        self::assertNull($log->after);
    }

    public function test_deactivate_unknown_user_throws(): void
    {
        $repo = new InMemoryUserRepository();
        $useCase = new DeactivateUserUseCase(
            $repo,
            new ImmediateTransactionManager(),
            $this->usersFactory($repo),
            $this->auditFactory(),
            $this->orgId,
        );

        $this->expectException(UserNotFoundException::class);
        $useCase->execute(null, 'missing');
    }
}
