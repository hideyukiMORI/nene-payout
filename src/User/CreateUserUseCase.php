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

final readonly class CreateUserUseCase implements CreateUserUseCaseInterface
{
    /**
     * @param Closure(DatabaseQueryExecutorInterface): UserRepositoryInterface $usersFactory
     * @param RequestScopedHolder<string> $orgId
     */
    public function __construct(
        private DatabaseTransactionManagerInterface $tx,
        private Closure $usersFactory,
        private AuditRecorderFactoryInterface $auditFactory,
        private RequestScopedHolder $orgId,
    ) {
    }

    public function execute(?string $actorUserId, CreateUserInput $input): User
    {
        $organizationId = $this->orgId->get();
        $id = Ulid::generate();

        return $this->tx->transactional(function (DatabaseQueryExecutorInterface $exec) use ($actorUserId, $organizationId, $id, $input): User {
            $users = ($this->usersFactory)($exec);

            if ($users->existsByEmail($input->email)) {
                throw new UserEmailConflictException($input->email);
            }

            // Invite: no password is set until the user activates (status `invited`).
            $users->save(new User(
                id: $id,
                email: $input->email,
                passwordHash: '',
                role: $input->role,
                organizationId: $organizationId,
                status: 'invited',
            ));

            $created = $users->findById($id);

            if ($created === null) {
                throw new LogicException('User disappeared immediately after creation.');
            }

            $this->auditFactory->forExecutor($exec)->record(new AuditEvent(
                action: 'user.created',
                entityType: 'user',
                entityId: $id,
                actorId: $actorUserId,
                organizationId: $organizationId,
                before: null,
                after: UserResponse::toArray($created),
                id: Ulid::generate(),
            ));

            return $created;
        });
    }
}
