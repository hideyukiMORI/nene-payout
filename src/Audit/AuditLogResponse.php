<?php

declare(strict_types=1);

namespace NenePayout\Audit;

final class AuditLogResponse
{
    /**
     * @return array<string, mixed>
     */
    public static function toArray(AuditLog $log): array
    {
        return [
            'id'              => $log->id,
            'actor_user_id'   => $log->actorUserId,
            'organization_id' => $log->organizationId,
            'action'          => $log->action,
            'entity_type'     => $log->entityType,
            'entity_id'       => $log->entityId,
            'before_json'     => $log->before,
            'after_json'      => $log->after,
            'request_id'      => $log->requestId,
            'created_at'      => $log->createdAt,
            'actor_email'     => $log->actorEmail,
        ];
    }
}
