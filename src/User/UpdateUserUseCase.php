<?php

declare(strict_types=1);

namespace NenePayout\User;

use Closure;
use LogicException;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Database\DatabaseTransactionManagerInterface;
use Nene2\Http\RequestScopedHolder;
use NenePayout\Audit\AuditRecorderInterface;
use NenePayout\Auth\User;

final readonly class UpdateUserUseCase implements UpdateUserUseCaseInterface
{
    /**
     * @param Closure(DatabaseQueryExecutorInterface): UserRepositoryInterface $usersFactory
     * @param Closure(DatabaseQueryExecutorInterface): AuditRecorderInterface $auditFactory
     * @param RequestScopedHolder<string> $orgId
     */
    public function __construct(
        private UserRepositoryInterface $users,
        private DatabaseTransactionManagerInterface $tx,
        private Closure $usersFactory,
        private Closure $auditFactory,
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

            ($this->auditFactory)($exec)->record(
                $actorUserId,
                $organizationId,
                'user.updated',
                'user',
                $id,
                UserResponse::toArray($existing),
                UserResponse::toArray($updated),
            );

            return $updated;
        });
    }
}
