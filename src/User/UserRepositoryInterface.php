<?php

declare(strict_types=1);

namespace NenePayout\User;

use NenePayout\Auth\User;

/**
 * Tenant-scoped user persistence for organization user management.
 * Implementations force the organization from the request-scoped holder and
 * exclude deactivated users from reads (ADR 0013, 0018). Email uniqueness is
 * global (the `users.email` unique index), so `existsByEmail` ignores tenant.
 */
interface UserRepositoryInterface
{
    public function findById(string $id): ?User;

    /** @return list<User> */
    public function findAll(int $limit, int $offset): array;

    public function count(): int;

    public function existsByEmail(string $email): bool;

    public function save(User $user): void;

    public function update(User $user): void;
}
