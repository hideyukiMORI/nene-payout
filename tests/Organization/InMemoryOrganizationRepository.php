<?php

declare(strict_types=1);

namespace NenePayout\Tests\Organization;

use NenePayout\Organization\Organization;
use NenePayout\Organization\OrganizationRepositoryInterface;

/**
 * In-memory test double for OrganizationRepositoryInterface. Lives in tests/ and
 * is never shipped (domain-layer policy).
 */
final class InMemoryOrganizationRepository implements OrganizationRepositoryInterface
{
    /** @var list<Organization> */
    private array $organizations;

    public function __construct(Organization ...$organizations)
    {
        $this->organizations = array_values($organizations);
    }

    public function findById(string $id): ?Organization
    {
        foreach ($this->organizations as $org) {
            if ($org->id === $id) {
                return $org;
            }
        }

        return null;
    }

    public function findBySlug(string $slug): ?Organization
    {
        foreach ($this->organizations as $org) {
            if ($org->slug === $slug) {
                return $org;
            }
        }

        return null;
    }

    public function findByCustomDomain(string $domain): ?Organization
    {
        foreach ($this->organizations as $org) {
            if ($org->customDomain === $domain) {
                return $org;
            }
        }

        return null;
    }
}
