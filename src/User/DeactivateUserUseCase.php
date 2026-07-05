<?php

declare(strict_types=1);

namespace NenePayout\User;

use Closure;
use Nene2\Audit\AuditEvent;
use Nene2\Audit\AuditRecorderFactoryInterface;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Database\DatabaseTransactionManagerInterface;
use Nene2\Http\RequestScopedHolder;
use NenePayout\Auth\User;
use NenePayout\Support\Ulid;

final readonly class DeactivateUserUseCase implements DeactivateUserUseCaseInterface
{
    /**
     * @param Closure(DatabaseQueryExecutorInterface): UserRepositoryInterface $usersFactory
     * @param RequestScopedHolder<string> $orgId
     */
    public function __construct(
        private UserRepositoryInterface $users,
        private DatabaseTransactionManagerInterface $tx,
        private Closure $usersFactory,
        private AuditRecorderFactoryInterface $auditFactory,
        private RequestScopedHolder $orgId,
    ) {
    }

    public function execute(?string $actorUserId, string $id): User
    {
        $existing = $this->users->findById($id);

        if ($existing === null) {
            throw new UserNotFoundException($id);
        }

        $organizationId = $this->orgId->get();

        $deactivated = new User(
            id: $id,
            email: $existing->email,
            passwordHash: $existing->passwordHash,
            role: $existing->role,
            organizationId: $organizationId,
            status: 'deactivated',
            createdAt: $existing->createdAt,
        );

        return $this->tx->transactional(function (DatabaseQueryExecutorInterface $exec) use ($actorUserId, $organizationId, $id, $existing, $deactivated): User {
            $users = ($this->usersFactory)($exec);
            $users->update($deactivated);

            // Soft delete: `after` is null (ADR 0011 / audit-logging.md).
            $this->auditFactory->forExecutor($exec)->record(new AuditEvent(
                action: 'user.deactivated',
                entityType: 'user',
                entityId: $id,
                actorId: $actorUserId,
                organizationId: $organizationId,
                before: UserResponse::toArray($existing),
                after: null,
                id: Ulid::generate(),
            ));

            return $deactivated;
        });
    }
}
