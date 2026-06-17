<?php

declare(strict_types=1);

namespace NenePayout\Organization;

/**
 * Read access to organizations used by tenant resolution and self-service
 * settings, plus the cross-tenant superadmin management (list/create/update/
 * deactivate; /api/v1/organizations).
 */
interface OrganizationRepositoryInterface
{
    public function findById(string $id): ?Organization;

    public function findBySlug(string $slug): ?Organization;

    public function findByCustomDomain(string $domain): ?Organization;

    /**
     * Cross-tenant list (superadmin).
     *
     * @return list<Organization>
     */
    public function findAll(int $limit, int $offset): array;

    /** Cross-tenant count (superadmin). */
    public function count(): int;

    public function existsBySlug(string $slug): bool;

    public function existsByCustomDomain(string $domain): bool;

    /** Inserts a new organization (superadmin). */
    public function save(Organization $organization): void;

    /** Persists mutable columns (name, custom_domain, is_active) for an existing organization. */
    public function update(Organization $organization): void;
}
