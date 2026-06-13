<?php

declare(strict_types=1);

namespace NenePayout\Audit;

final class AuditLogFilterFactory
{
    /**
     * @param array<string, mixed> $query
     */
    public static function fromQueryParams(array $query): AuditLogFilter
    {
        return new AuditLogFilter(
            entityType: self::str($query, 'entity_type'),
            entityId: self::str($query, 'entity_id'),
            actorUserId: self::str($query, 'actor_user_id'),
            action: self::str($query, 'action'),
            createdFrom: self::str($query, 'from'),
            createdTo: self::str($query, 'to'),
        );
    }

    /**
     * @param array<string, mixed> $query
     */
    private static function str(array $query, string $key): ?string
    {
        $value = $query[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }
}
