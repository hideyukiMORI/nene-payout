<?php

declare(strict_types=1);

namespace NenePayout\Organization;

/**
 * A tenant. Every tenant-scoped row carries `organization_id` (this entity's id).
 * See docs/explanation/multi-tenancy.md (ADR 0004, 0018).
 */
final readonly class Organization
{
    public function __construct(
        public string $slug,
        public string $name,
        public bool $isActive,
        public ?string $id = null,
        public ?string $customDomain = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }
}
