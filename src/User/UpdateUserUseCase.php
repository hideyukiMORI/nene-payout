<?php

declare(strict_types=1);

namespace NenePayout\User;

use Closure;
use LogicException;
use Nene2\Audit\AuditEvent;
use Nene2\Audit\AuditRecorderFactoryInterface;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Database\DatabaseTransactionManagerInterface;
use Nene2\Http\RequestScopedHolder;
use NenePayout\Auth\User;
use NenePayout\Support\Ulid;

final readonly class UpdateUserUseCase implements UpdateUserUseCaseInterface
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

    public function execute(?string $actorUserId, string $id, UpdateUserInput $input): User
    {
        $existing = $this->users->findById($id);

        if ($existing === null) {
            throw new UserNotFoundException($id);
        }

        $organizationId = $this->orgId->get();

        return $this->tx->transactional(function (DatabaseQueryExecutorInterface $exec) use ($actorUserId, $organizationId, $id, $input, $existing): User {
            $users = ($this->usersFactory)($exec);

            $users->update(new User(
                id: $id,
                email: $existing->email,
                passwordHash: $existing->passwordHash,
                role: $input->role,
                organizationId: $organizationId,
                status: $existing->status,
                createdAt: $existing->createdAt,
            ));

            $updated = $users->findById($id);

            if ($updated === null) {
                throw new LogicException('User disappeared immediately after update.');
            }

            $this->auditFactory->forExecutor($exec)->record(new AuditEvent(
                action: 'user.updated',
                entityType: 'user',
                entityId: $id,
                actorId: $actorUserId,
                organizationId: $organizationId,
                before: UserResponse::toArray($existing),
                after: UserResponse::toArray($updated),
                id: Ulid::generate(),
            ));

            return $updated;
        });
    }
}
