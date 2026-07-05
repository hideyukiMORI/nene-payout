<?php

declare(strict_types=1);

namespace NenePayout\Audit;

final readonly class ListAuditLogsUseCase implements ListAuditLogsUseCaseInterface
{
    public function __construct(
        private AuditReadRepositoryInterface $repository,
    ) {
    }

    public function execute(AuditLogFilter $filter, int $limit, int $offset): ListAuditLogsOutput
    {
        return new ListAuditLogsOutput(
            items: $this->repository->findAll($filter, $limit, $offset),
            total: $this->repository->count($filter),
        );
    }
}
