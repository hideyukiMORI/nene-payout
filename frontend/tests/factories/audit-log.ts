import type { AuditLogDto } from '@/entities/audit-log/api-types'

export function auditLogDto(overrides: Partial<AuditLogDto> = {}): AuditLogDto {
  return {
    id: '01AUDIT00000000000000000001',
    actor_user_id: '01USER000000000000000000001',
    actor_email: 'admin@example.com',
    organization_id: '01ORG00000000000000000001',
    action: 'vendor.updated',
    entity_type: 'vendor',
    entity_id: '01VENDOR000000000000000001',
    request_id: 'req_abc',
    created_at: '2026-06-14T01:00:00Z',
    ...overrides,
  }
}
