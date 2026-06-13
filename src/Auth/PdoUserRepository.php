<?php

declare(strict_types=1);

namespace NenePayout\Auth;

use Nene2\Database\DatabaseQueryExecutorInterface;

final readonly class PdoUserRepository implements UserRepositoryInterface
{
    private const COLUMNS = 'id, organization_id, email, password_hash, role, status, created_at, updated_at';

    public function __construct(
        private DatabaseQueryExecutorInterface $query,
    ) {
    }

    public function findByEmail(string $email): ?User
    {
        $row = $this->query->fetchOne(
            'SELECT ' . self::COLUMNS . ' FROM users WHERE email = ?',
            [$email],
        );

        return $row !== null ? $this->mapRow($row) : null;
    }

    public function findById(string $id): ?User
    {
        $row = $this->query->fetchOne(
            'SELECT ' . self::COLUMNS . ' FROM users WHERE id = ?',
            [$id],
        );

        return $row !== null ? $this->mapRow($row) : null;
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
