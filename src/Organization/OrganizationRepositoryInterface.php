<?php

declare(strict_types=1);

namespace NenePayout\Organization;

/**
 * Read access to organizations used by tenant resolution. CRUD is added with the
 * superadmin organization-management endpoints in a later slice.
 */
interface OrganizationRepositoryInterface
{
    public function findById(string $id): ?Organization;

    public function findBySlug(string $slug): ?Organization;

    public function findByCustomDomain(string $domain): ?Organization;
}
