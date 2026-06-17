<?php

declare(strict_types=1);

namespace NenePayout\Tests\User;

use NenePayout\Auth\User;
use NenePayout\User\UserRepositoryInterface;

final class InMemoryUserRepository implements UserRepositoryInterface
{
    /** @var list<User> */
    private array $users;

    public function __construct(User ...$users)
    {
        $this->users = array_values($users);
    }

    public function findById(string $id): ?User
    {
        foreach ($this->users as $user) {
            if ($user->id === $id && $user->status !== 'deactivated') {
                return $user;
            }
        }

        return null;
    }

    /** @return list<User> */
    public function findAll(int $limit, int $offset): array
    {
        return array_slice($this->active(), $offset, $limit);
    }

    public function count(): int
    {
        return count($this->active());
    }

    public function existsByEmail(string $email): bool
    {
        foreach ($this->users as $user) {
            if ($user->email === $email) {
                return true;
            }
        }

        return false;
    }

    public function save(User $user): void
    {
        $this->users[] = $user;
    }

    public function update(User $user): void
    {
        foreach ($this->users as $i => $existing) {
            if ($existing->id === $user->id) {
                $this->users[$i] = $user;

                return;
            }
        }
    }

    /** @return list<User> */
    private function active(): array
    {
        return array_values(array_filter(
            $this->users,
            static fn (User $user): bool => $user->status !== 'deactivated',
        ));
    }
}
