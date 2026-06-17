<?php

declare(strict_types=1);

namespace NenePayout\Organization;

use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Http\ClockInterface;

final readonly class PdoOrganizationRepository implements OrganizationRepositoryInterface
{
    private const COLUMNS = 'id, slug, name, custom_domain, is_active, created_at, updated_at';

    public function __construct(
        private DatabaseQueryExecutorInterface $query,
        private ClockInterface $clock,
    ) {
    }

    public function findById(string $id): ?Organization
    {
        $row = $this->query->fetchOne(
            'SELECT ' . self::COLUMNS . ' FROM organizations WHERE id = ?',
            [$id],
        );

        return $row !== null ? $this->mapRow($row) : null;
    }

    public function findBySlug(string $slug): ?Organization
    {
        $row = $this->query->fetchOne(
            'SELECT ' . self::COLUMNS . ' FROM organizations WHERE slug = ?',
            [$slug],
        );

        return $row !== null ? $this->mapRow($row) : null;
    }

    public function findByCustomDomain(string $domain): ?Organization
    {
        $row = $this->query->fetchOne(
            'SELECT ' . self::COLUMNS . ' FROM organizations WHERE custom_domain = ?',
            [$domain],
        );

        return $row !== null ? $this->mapRow($row) : null;
    }

    /** @return list<Organization> */
    public function findAll(int $limit, int $offset): array
    {
        $rows = $this->query->fetchAll(
            'SELECT ' . self::COLUMNS . ' FROM organizations ORDER BY created_at DESC, id DESC LIMIT ? OFFSET ?',
            [$limit, $offset],
        );

        return array_map(fn (array $row): Organization => $this->mapRow($row), $rows);
    }

    public function count(): int
    {
        $row = $this->query->fetchOne('SELECT COUNT(*) AS cnt FROM organizations', []);

        return $row !== null ? (int) $row['cnt'] : 0;
    }

    public function existsBySlug(string $slug): bool
    {
        return $this->query->fetchOne('SELECT 1 AS hit FROM organizations WHERE slug = ?', [$slug]) !== null;
    }

    public function existsByCustomDomain(string $domain): bool
    {
        return $this->query->fetchOne('SELECT 1 AS hit FROM organizations WHERE custom_domain = ?', [$domain]) !== null;
    }

    public function save(Organization $organization): void
    {
        $now = $this->clock->now()->format('Y-m-d H:i:s');

        $this->query->execute(
            'INSERT INTO organizations
                (id, slug, name, custom_domain, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                $organization->id,
                $organization->slug,
                $organization->name,
                $organization->customDomain,
                $organization->isActive ? 1 : 0,
                $now,
                $now,
            ],
        );
    }

    public function update(Organization $organization): void
    {
        $now = $this->clock->now()->format('Y-m-d H:i:s');

        // Mutable columns only; `slug` is immutable (drives tenant resolution).
        $this->query->execute(
            'UPDATE organizations SET name = ?, custom_domain = ?, is_active = ?, updated_at = ? WHERE id = ?',
            [
                $organization->name,
                $organization->customDomain,
                $organization->isActive ? 1 : 0,
                $now,
                $organization->id,
            ],
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private function mapRow(array $row): Organization
    {
        return new Organization(
            slug: (string) $row['slug'],
            name: (string) $row['name'],
            isActive: (bool) (int) $row['is_active'],
            id: (string) $row['id'],
            customDomain: $row['custom_domain'] !== null ? (string) $row['custom_domain'] : null,
            createdAt: $row['created_at'] !== null ? (string) $row['created_at'] : null,
            updatedAt: $row['updated_at'] !== null ? (string) $row['updated_at'] : null,
        );
    }
}
