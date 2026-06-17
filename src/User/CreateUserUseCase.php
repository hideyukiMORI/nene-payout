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
use NenePayout\Support\Ulid;

final readonly class CreateUserUseCase implements CreateUserUseCaseInterface
{
    /**
     * @param Closure(DatabaseQueryExecutorInterface): UserRepositoryInterface $usersFactory
     * @param Closure(DatabaseQueryExecutorInterface): AuditRecorderInterface $auditFactory
     * @param RequestScopedHolder<string> $orgId
     */
    public function __construct(
        private DatabaseTransactionManagerInterface $tx,
        private Closure $usersFactory,
        private Closure $auditFactory,
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

            ($this->auditFactory)($exec)->record(
                $actorUserId,
                $organizationId,
                'user.created',
                'user',
                $id,
                null,
                UserResponse::toArray($created),
            );

            return $created;
        });
    }
}
