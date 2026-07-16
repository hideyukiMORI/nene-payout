<?php

declare(strict_types=1);

namespace NenePayout\Audit;

final readonly class ListAuditLogsOutput
{
    /**
     * @param list<AuditLogView> $items
     */
    public function __construct(
        public array $items,
        public int $total,
    ) {
    }
}
