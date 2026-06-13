<?php

declare(strict_types=1);

namespace NenePayout\Audit;

use Nene2\Http\ClockInterface;
use NenePayout\Support\Ulid;

final readonly class AuditRecorder implements AuditRecorderInterface
{
    public function __construct(
        private AuditLogRepositoryInterface $repository,
        private ClockInterface $clock,
    ) {
    }

    public function record(
        ?string $actorUserId,
        ?string $organizationId,
        string $action,
        string $entityType,
        ?string $entityId,
        ?array $before,
        ?array $after,
        ?string $requestId = null,
    ): void {
        $this->repository->append(new AuditLog(
            action: $action,
            entityType: $entityType,
            actorUserId: $actorUserId,
            organizationId: $organizationId,
            entityId: $entityId,
            before: $before,
            after: $after,
            requestId: $requestId,
            id: Ulid::generate(),
            // UTC instant from the injected clock (ADR 0012).
            createdAt: $this->clock->now()->format('Y-m-d H:i:s'),
        ));
    }
}
