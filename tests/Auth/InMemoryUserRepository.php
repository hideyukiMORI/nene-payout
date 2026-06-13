<?php

declare(strict_types=1);

namespace NenePayout\Tests\Auth;

use NenePayout\Auth\User;
use NenePayout\Auth\UserRepositoryInterface;

final class InMemoryUserRepository implements UserRepositoryInterface
{
    /** @var list<User> */
    private array $users;

    public function __construct(User ...$users)
    {
        $this->users = array_values($users);
    }

    public function findByEmail(string $email): ?User
    {
        foreach ($this->users as $user) {
            if ($user->email === $email) {
                return $user;
            }
        }

        return null;
    }

    public function findById(string $id): ?User
    {
        foreach ($this->users as $user) {
            if ($user->id === $id) {
                return $user;
            }
        }

        return null;
    }
}
