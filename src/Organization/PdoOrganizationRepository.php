<?php

declare(strict_types=1);

namespace NenePayout\Organization;

use Nene2\Database\DatabaseQueryExecutorInterface;

final readonly class PdoOrganizationRepository implements OrganizationRepositoryInterface
{
    private const COLUMNS = 'id, slug, name, custom_domain, is_active, created_at, updated_at';

    public function __construct(
        private DatabaseQueryExecutorInterface $query,
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
