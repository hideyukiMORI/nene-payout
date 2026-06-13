<?php

declare(strict_types=1);

namespace NenePayout\Audit;

/**
 * Append-only persistence for the audit trail (ADR 0011, 0013).
 *
 * Reads are scoped to the organization in the request-scoped holder. `append()`
 * carries the organization on the AuditLog itself, because writes also run on
 * holder-less paths (e.g. superadmin organization provisioning).
 */
interface AuditLogRepositoryInterface
{
    public function append(AuditLog $log): void;

    /** @return list<AuditLog> */
    public function findAll(AuditLogFilter $filter, int $limit, int $offset): array;

    public function count(AuditLogFilter $filter): int;
}
