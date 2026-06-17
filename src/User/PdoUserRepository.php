<?php

declare(strict_types=1);

namespace NenePayout\User;

use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Http\ClockInterface;
use Nene2\Http\RequestScopedHolder;
use NenePayout\Auth\User;

final readonly class PdoUserRepository implements UserRepositoryInterface
{
    private const COLUMNS = 'id, organization_id, email, password_hash, role, status, created_at, updated_at';

    /**
     * @param RequestScopedHolder<string> $orgId
     */
    public function __construct(
        private DatabaseQueryExecutorInterface $query,
        private RequestScopedHolder $orgId,
        private ClockInterface $clock,
    ) {
    }

    private function now(): string
    {
        return $this->clock->now()->format('Y-m-d H:i:s');
    }

    public function findById(string $id): ?User
    {
        $row = $this->query->fetchOne(
            'SELECT ' . self::COLUMNS . " FROM users WHERE id = ? AND organization_id = ? AND status <> 'deactivated'",
            [$id, $this->orgId->get()],
        );

        return $row !== null ? $this->mapRow($row) : null;
    }

    /** @return list<User> */
    public function findAll(int $limit, int $offset): array
    {
        $rows = $this->query->fetchAll(
            'SELECT ' . self::COLUMNS . " FROM users WHERE organization_id = ? AND status <> 'deactivated' ORDER BY created_at DESC, id DESC LIMIT ? OFFSET ?",
            [$this->orgId->get(), $limit, $offset],
        );

        return array_map(fn (array $row): User => $this->mapRow($row), $rows);
    }

    public function count(): int
    {
        $row = $this->query->fetchOne(
            "SELECT COUNT(*) AS cnt FROM users WHERE organization_id = ? AND status <> 'deactivated'",
            [$this->orgId->get()],
        );

        return $row !== null ? (int) $row['cnt'] : 0;
    }

    public function existsByEmail(string $email): bool
    {
        $row = $this->query->fetchOne('SELECT 1 AS hit FROM users WHERE email = ?', [$email]);

        return $row !== null;
    }

    public function save(User $user): void
    {
        $now = $this->now();

        $this->query->execute(
            'INSERT INTO users
                (id, organization_id, email, password_hash, role, status, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $user->id,
                $this->orgId->get(),
                $user->email,
                $user->passwordHash,
                $user->role,
                $user->status,
                $now,
                $now,
            ],
        );
    }

    public function update(User $user): void
    {
        $this->query->execute(
            'UPDATE users SET role = ?, status = ?, updated_at = ? WHERE id = ? AND organization_id = ?',
            [
                $user->role,
                $user->status,
                $this->now(),
                $user->id,
                $this->orgId->get(),
            ],
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private function mapRow(array $row): User
    {
        return new User(
            id: (string) $row['id'],
            email: (string) $row['email'],
            passwordHash: (string) $row['password_hash'],
            role: (string) $row['role'],
            organizationId: $row['organization_id'] !== null ? (string) $row['organization_id'] : null,
            status: (string) $row['status'],
            createdAt: $row['created_at'] !== null ? (string) $row['created_at'] : null,
            updatedAt: $row['updated_at'] !== null ? (string) $row['updated_at'] : null,
        );
    }
}
