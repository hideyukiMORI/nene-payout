<?php

declare(strict_types=1);

namespace NenePayout\Audit;

interface ListAuditLogsUseCaseInterface
{
    public function execute(AuditLogFilter $filter, int $limit, int $offset): ListAuditLogsOutput;
}
