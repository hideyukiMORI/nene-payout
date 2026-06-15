import type { AuditLogId } from './ids'

export interface AuditLog {
  id: AuditLogId
  actorUserId: string | null
  actorEmail: string | null
  organizationId: string | null
  action: string
  entityType: string
  entityId: string
  requestId: string | null
  createdAt: string
}

export interface AuditLogList {
  items: AuditLog[]
  limit: number
  offset: number
  total: number | null
}
