<?php

declare(strict_types=1);

namespace NenePayout\Organization;

/**
 * Read access to organizations used by tenant resolution, plus the self-service
 * settings update (name only). Cross-tenant superadmin CRUD is added with the
 * /organizations endpoints in a later slice.
 */
interface OrganizationRepositoryInterface
{
    public function findById(string $id): ?Organization;

    public function findBySlug(string $slug): ?Organization;

    public function findByCustomDomain(string $domain): ?Organization;

    /** Persists mutable settings (currently `name`) for an existing organization. */
    public function update(Organization $organization): void;
}
