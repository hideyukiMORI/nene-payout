<?php

declare(strict_types=1);

namespace NenePayout\Audit;

/**
 * One recorded mutating operation (ADR 0011 / audit-logging.md).
 *
 * `before` / `after` are sanitized snapshots (no PAN, tokens, or secrets).
 * `before` is null for create; `after` is null for void/delete.
 */
final readonly class AuditLog
{
    /**
     * @param array<string, mixed>|null $before
     * @param array<string, mixed>|null $after
     */
    public function __construct(
        public string $action,
        public string $entityType,
        public ?string $actorUserId = null,
        public ?string $organizationId = null,
        public ?string $entityId = null,
        public ?array $before = null,
        public ?array $after = null,
        public ?string $requestId = null,
        public ?string $id = null,
        public ?string $createdAt = null,
        /** Resolved at read time only; never persisted. */
        public ?string $actorEmail = null,
    ) {
    }
}
