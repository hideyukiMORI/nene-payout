<?php

declare(strict_types=1);

namespace NenePayout\Audit;

use Nene2\Audit\AuditEvent;
use Nene2\Audit\AuditEventRepositoryInterface;
use Nene2\Audit\AuditQuery;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Http\RequestScopedHolder;

/**
 * Read side of the audit trail. Persistence is the framework's
 * {@see AuditEventRepositoryInterface} (`PdoAuditEventRepository`); this class
 * adds the two product-specific read concerns ADR 0014 leaves out of the
 * framework contract:
 *
 * - **organization scoping** — every read is constrained to the org in the
 *   request-scoped holder (tenant isolation), mapped onto
 *   {@see AuditQuery::$organizationId};
 * - **actor email** — resolved with a single `users` lookup and attached as a
 *   view field, never persisted on the trail.
 *
 * The default {@see AuditQuery} sort (`occurred_at`/`created_at` DESC, id DESC)
 * reproduces the previous hand-rolled `ORDER BY created_at DESC, id DESC`.
 */
final readonly class PdoAuditReadRepository implements AuditReadRepositoryInterface
{
    /**
     * @param RequestScopedHolder<string> $orgId
     */
    public function __construct(
        private AuditEventRepositoryInterface $events,
        private DatabaseQueryExecutorInterface $query,
        private RequestScopedHolder $orgId,
    ) {
    }

    /** @return list<AuditLogView> */
    public function findAll(AuditLogFilter $filter, int $limit, int $offset): array
    {
        $events = $this->events->query($this->toQuery($filter), $limit, $offset);
        $emails = $this->resolveActorEmails($events);

        return array_map(
            static fn (AuditEvent $event): AuditLogView => new AuditLogView(
                $event,
                $event->actorId !== null ? ($emails[(string) $event->actorId] ?? null) : null,
            ),
            $events,
        );
    }

    public function count(AuditLogFilter $filter): int
    {
        return $this->events->count($this->toQuery($filter));
    }

    private function toQuery(AuditLogFilter $filter): AuditQuery
    {
        return new AuditQuery(
            organizationId: $this->orgId->get(),
            entityType: $filter->entityType,
            entityId: $filter->entityId,
            action: $filter->action,
            actorId: $filter->actorUserId,
            occurredFrom: $filter->createdFrom,
            occurredTo: $filter->createdTo,
        );
    }

    /**
     * @param list<AuditEvent> $events
     * @return array<string, string>
     */
    private function resolveActorEmails(array $events): array
    {
        $ids = [];

        foreach ($events as $event) {
            if ($event->actorId !== null) {
                $ids[(string) $event->actorId] = true;
            }
        }

        if ($ids === []) {
            return [];
        }

        $keys = array_keys($ids);
        $placeholders = implode(', ', array_fill(0, count($keys), '?'));

        $rows = $this->query->fetchAll(
            'SELECT id, email FROM users WHERE id IN (' . $placeholders . ')',
            $keys,
        );

        $emails = [];

        foreach ($rows as $row) {
            $emails[(string) $row['id']] = (string) $row['email'];
        }

        return $emails;
    }
}
