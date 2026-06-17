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

    public function update(Organization $organization): void
    {
        $now = $this->clock->now()->format('Y-m-d H:i:s');

        // Only `name` is mutable through self-service settings; slug/custom_domain
        // drive tenant resolution and are managed by superadmin endpoints.
        $this->query->execute(
            'UPDATE organizations SET name = ?, updated_at = ? WHERE id = ?',
            [$organization->name, $now, $organization->id],
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
