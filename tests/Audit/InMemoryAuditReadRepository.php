<?php

declare(strict_types=1);

namespace NenePayout\Tests\Audit;

use NenePayout\Audit\AuditLogFilter;
use NenePayout\Audit\AuditLogView;
use NenePayout\Audit\AuditReadRepositoryInterface;

final class InMemoryAuditReadRepository implements AuditReadRepositoryInterface
{
    /** @var list<AuditLogView> */
    public array $views = [];

    public function add(AuditLogView $view): void
    {
        $this->views[] = $view;
    }

    /** @return list<AuditLogView> */
    public function findAll(AuditLogFilter $filter, int $limit, int $offset): array
    {
        return array_slice($this->match($filter), $offset, $limit);
    }

    public function count(AuditLogFilter $filter): int
    {
        return count($this->match($filter));
    }

    /** @return list<AuditLogView> */
    private function match(AuditLogFilter $filter): array
    {
        return array_values(array_filter(
            $this->views,
            static function (AuditLogView $view) use ($filter): bool {
                if ($filter->entityType !== null && $view->event->entityType !== $filter->entityType) {
                    return false;
                }

                if ($filter->action !== null && $view->event->action !== $filter->action) {
                    return false;
                }

                return true;
            },
        ));
    }
}
