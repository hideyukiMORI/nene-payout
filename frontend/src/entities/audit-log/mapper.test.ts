import { describe, expect, it } from 'vitest'
import { mapAuditLogDtoToModel, mapAuditLogListDtoToModel } from './mapper'
import type { AuditLogDto } from './api-types'

const dto: AuditLogDto = {
  id: '01AUDIT00000000000000000001',
  actor_user_id: '01USER000000000000000000001',
  actor_email: 'admin@example.com',
  organization_id: '01ORG00000000000000000001',
  action: 'vendor.updated',
  entity_type: 'vendor',
  entity_id: '01VENDOR000000000000000001',
  request_id: 'req_abc',
  created_at: '2026-06-14T01:00:00Z',
}

describe('mapAuditLogDtoToModel', () => {
  it('maps snake_case DTO fields to the model', () => {
    expect(mapAuditLogDtoToModel(dto)).toEqual({
      id: '01AUDIT00000000000000000001',
      actorUserId: '01USER000000000000000000001',
      actorEmail: 'admin@example.com',
      organizationId: '01ORG00000000000000000001',
      action: 'vendor.updated',
      entityType: 'vendor',
      entityId: '01VENDOR000000000000000001',
      requestId: 'req_abc',
      createdAt: '2026-06-14T01:00:00Z',
    })
  })

  it('defaults optional/nullable fields to null', () => {
    const model = mapAuditLogDtoToModel({
      id: '01AUDIT00000000000000000002',
      action: 'system.event',
      entity_type: 'system',
      entity_id: '-',
      created_at: '2026-06-14T02:00:00Z',
    })

    expect(model.actorUserId).toBeNull()
    expect(model.actorEmail).toBeNull()
    expect(model.requestId).toBeNull()
  })
})

describe('mapAuditLogListDtoToModel', () => {
  it('maps items and pagination, defaulting total to null', () => {
    const list = mapAuditLogListDtoToModel({ items: [dto], limit: 20, offset: 0 })

    expect(list.items).toHaveLength(1)
    expect(list.total).toBeNull()
  })
})
