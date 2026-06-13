<?php

declare(strict_types=1);

namespace NenePayout\Audit;

/**
 * Records a mutating operation in the audit trail (ADR 0011). Called inside the
 * UseCase, within the same transaction as the mutation.
 */
interface AuditRecorderInterface
{
    /**
     * @param array<string, mixed>|null $before sanitized snapshot before (null for create)
     * @param array<string, mixed>|null $after  sanitized snapshot after  (null for void/delete)
     */
    public function record(
        ?string $actorUserId,
        ?string $organizationId,
        string $action,
        string $entityType,
        ?string $entityId,
        ?array $before,
        ?array $after,
        ?string $requestId = null,
    ): void;
}
