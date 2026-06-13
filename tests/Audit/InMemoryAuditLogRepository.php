<?php

declare(strict_types=1);

namespace NenePayout\Tests\Audit;

use NenePayout\Audit\AuditLog;
use NenePayout\Audit\AuditLogFilter;
use NenePayout\Audit\AuditLogRepositoryInterface;

final class InMemoryAuditLogRepository implements AuditLogRepositoryInterface
{
    /** @var list<AuditLog> */
    public array $appended = [];

    public function append(AuditLog $log): void
    {
        $this->appended[] = $log;
    }

    /** @return list<AuditLog> */
    public function findAll(AuditLogFilter $filter, int $limit, int $offset): array
    {
        return array_slice($this->match($filter), $offset, $limit);
    }

    public function count(AuditLogFilter $filter): int
    {
        return count($this->match($filter));
    }

    /** @return list<AuditLog> */
    private function match(AuditLogFilter $filter): array
    {
        return array_values(array_filter(
            $this->appended,
            static function (AuditLog $log) use ($filter): bool {
                if ($filter->entityType !== null && $log->entityType !== $filter->entityType) {
                    return false;
                }

                if ($filter->action !== null && $log->action !== $filter->action) {
                    return false;
                }

                return true;
            },
        ));
    }
}
