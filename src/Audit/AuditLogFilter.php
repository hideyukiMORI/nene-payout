<?php

declare(strict_types=1);

namespace NenePayout\Audit;

/**
 * Read-side filter for the audit trail. Every field is optional; null does not
 * constrain. Organization scoping is applied separately by the repository
 * (request-scoped holder) and is never part of this filter.
 */
final readonly class AuditLogFilter
{
    public function __construct(
        public ?string $entityType = null,
        public ?string $entityId = null,
        public ?string $actorUserId = null,
        public ?string $action = null,
        public ?string $createdFrom = null,
        public ?string $createdTo = null,
    ) {
    }
}
