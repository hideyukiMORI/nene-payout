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

    /** @return list<Organization> */
    public function findAll(int $limit, int $offset): array
    {
        return array_slice($this->organizations, $offset, $limit);
    }

    public function count(): int
    {
        return count($this->organizations);
    }

    public function existsBySlug(string $slug): bool
    {
        foreach ($this->organizations as $org) {
            if ($org->slug === $slug) {
                return true;
            }
        }

        return false;
    }

    public function existsByCustomDomain(string $domain): bool
    {
        foreach ($this->organizations as $org) {
            if ($org->customDomain === $domain) {
                return true;
            }
        }

        return false;
    }

    public function save(Organization $organization): void
    {
        $this->organizations[] = $organization;
    }

    public function update(Organization $organization): void
    {
        foreach ($this->organizations as $i => $existing) {
            if ($existing->id === $organization->id) {
                $this->organizations[$i] = $organization;

                return;
            }
        }
    }
}
