<?php

declare(strict_types=1);

namespace NenePayout\Audit;

/**
 * Product read layer over the framework audit trail (ADR 0014).
 *
 * Persistence is the framework's `Nene2\Audit\PdoAuditEventRepository`; this
 * contract owns the two read concerns ADR 0014 intentionally keeps out of the
 * framework: organization scoping (tenant isolation) and actor-email resolution.
 * Reads are always constrained to the organization in the request-scoped holder.
 */
interface AuditReadRepositoryInterface
{
    /** @return list<AuditLogView> */
    public function findAll(AuditLogFilter $filter, int $limit, int $offset): array;

    public function count(AuditLogFilter $filter): int;
}
