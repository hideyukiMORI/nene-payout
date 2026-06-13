<?php

declare(strict_types=1);

namespace NenePayout\Auth;

/**
 * User read access for authentication. Full CRUD (admin user management) is
 * added with the users endpoints in a later slice.
 */
interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;

    public function findById(string $id): ?User;
}
