<?php

declare(strict_types=1);

namespace NenePayout\Audit;

use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Http\RequestScopedHolder;

final readonly class PdoAuditLogRepository implements AuditLogRepositoryInterface
{
    /**
     * @param RequestScopedHolder<string> $orgId
     */
    public function __construct(
        private DatabaseQueryExecutorInterface $query,
        private RequestScopedHolder $orgId,
    ) {
    }

    public function append(AuditLog $log): void
    {
        $this->query->execute(
            'INSERT INTO audit_logs
                (id, actor_user_id, organization_id, action, entity_type, entity_id, before_json, after_json, request_id, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $log->id,
                $log->actorUserId,
                $log->organizationId,
                $log->action,
                $log->entityType,
                $log->entityId,
                $log->before !== null ? json_encode($log->before, JSON_THROW_ON_ERROR) : null,
                $log->after !== null ? json_encode($log->after, JSON_THROW_ON_ERROR) : null,
                $log->requestId,
                $log->createdAt,
            ],
        );
    }

    /** @return list<AuditLog> */
    public function findAll(AuditLogFilter $filter, int $limit, int $offset): array
    {
        [$where, $params] = $this->where($filter);

        $rows = $this->query->fetchAll(
            'SELECT a.id, a.actor_user_id, a.organization_id, a.action, a.entity_type, a.entity_id,
                    a.before_json, a.after_json, a.request_id, a.created_at, u.email AS actor_email
             FROM audit_logs a
             LEFT JOIN users u ON u.id = a.actor_user_id
             WHERE ' . $where . '
             ORDER BY a.created_at DESC, a.id DESC
             LIMIT ? OFFSET ?',
            [...$params, $limit, $offset],
        );

        return array_map(fn (array $row): AuditLog => $this->mapRow($row), $rows);
    }

    public function count(AuditLogFilter $filter): int
    {
        [$where, $params] = $this->where($filter);

        $row = $this->query->fetchOne(
            'SELECT COUNT(*) AS cnt FROM audit_logs a WHERE ' . $where,
            $params,
        );

        return $row !== null ? (int) $row['cnt'] : 0;
    }

    /**
     * @return array{0: string, 1: list<string>}
     */
    private function where(AuditLogFilter $filter): array
    {
        $clauses = ['a.organization_id = ?'];
        $params = [$this->orgId->get()];

        if ($filter->entityType !== null) {
            $clauses[] = 'a.entity_type = ?';
            $params[] = $filter->entityType;
        }

        if ($filter->entityId !== null) {
            $clauses[] = 'a.entity_id = ?';
            $params[] = $filter->entityId;
        }

        if ($filter->actorUserId !== null) {
            $clauses[] = 'a.actor_user_id = ?';
            $params[] = $filter->actorUserId;
        }

        if ($filter->action !== null) {
            $clauses[] = 'a.action = ?';
            $params[] = $filter->action;
        }

        if ($filter->createdFrom !== null) {
            $clauses[] = 'a.created_at >= ?';
            $params[] = $filter->createdFrom;
        }

        if ($filter->createdTo !== null) {
            $clauses[] = 'a.created_at <= ?';
            $params[] = $filter->createdTo;
        }

        return [implode(' AND ', $clauses), $params];
    }

    /**
     * @param array<string, mixed> $row
     */
    private function mapRow(array $row): AuditLog
    {
        return new AuditLog(
            action: (string) $row['action'],
            entityType: (string) $row['entity_type'],
            actorUserId: $row['actor_user_id'] !== null ? (string) $row['actor_user_id'] : null,
            organizationId: $row['organization_id'] !== null ? (string) $row['organization_id'] : null,
            entityId: $row['entity_id'] !== null ? (string) $row['entity_id'] : null,
            before: self::decode($row['before_json'] ?? null),
            after: self::decode($row['after_json'] ?? null),
            requestId: $row['request_id'] !== null ? (string) $row['request_id'] : null,
            id: (string) $row['id'],
            createdAt: (string) $row['created_at'],
            actorEmail: ($row['actor_email'] ?? null) !== null ? (string) $row['actor_email'] : null,
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function decode(mixed $json): ?array
    {
        if (!is_string($json) || $json === '') {
            return null;
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : null;
    }
}
