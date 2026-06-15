import type { AuditLogDto, AuditLogListDto } from './api-types'
import { toAuditLogId } from './ids'
import type { AuditLog, AuditLogList } from './model'

export function mapAuditLogDtoToModel(dto: AuditLogDto): AuditLog {
  return {
    id: toAuditLogId(dto.id),
    actorUserId: dto.actor_user_id ?? null,
    actorEmail: dto.actor_email ?? null,
    organizationId: dto.organization_id ?? null,
    action: dto.action,
    entityType: dto.entity_type,
    entityId: dto.entity_id,
    requestId: dto.request_id ?? null,
    createdAt: dto.created_at,
  }
}

export function mapAuditLogListDtoToModel(dto: AuditLogListDto): AuditLogList {
  return {
    items: dto.items.map(mapAuditLogDtoToModel),
    limit: dto.limit,
    offset: dto.offset,
    total: dto.total ?? null,
  }
}
