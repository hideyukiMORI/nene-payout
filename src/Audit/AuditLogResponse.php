<?php

declare(strict_types=1);

namespace NenePayout\Audit;

final class AuditLogResponse
{
    /**
     * @return array<string, mixed>
     */
    public static function toArray(AuditLogView $view): array
    {
        $event = $view->event;

        return [
            'id'              => $event->id,
            'actor_user_id'   => $event->actorId,
            'organization_id' => $event->organizationId,
            'action'          => $event->action,
            'entity_type'     => $event->entityType,
            'entity_id'       => $event->entityId,
            'before_json'     => $event->before,
            'after_json'      => $event->after,
            // request_id lives in the framework `metadata` receptacle, mapped to
            // the physical `request_id` column via AuditTableConfig (ADR 0014).
            'request_id'      => $event->metadata['request_id'] ?? null,
            'created_at'      => $event->occurredAt,
            'actor_email'     => $view->actorEmail,
        ];
    }
}
