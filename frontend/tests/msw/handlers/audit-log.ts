import { http, HttpResponse } from 'msw'
import type { AuditLogListDto } from '@/entities/audit-log/api-types'
import { auditLogDto } from '../../factories/audit-log'

export const auditLogHandlers = [
  http.get('*/api/v1/audit-logs', () => {
    const body: AuditLogListDto = {
      items: [
        auditLogDto(),
        auditLogDto({
          id: '01AUDIT00000000000000000002',
          action: 'received_invoice.created',
          entity_type: 'received_invoice',
          entity_id: '01INV0000000000000000000001',
        }),
      ],
      limit: 20,
      offset: 0,
      total: 2,
    }
    return HttpResponse.json(body)
  }),
]

export const emptyAuditLogHandlers = [
  http.get('*/api/v1/audit-logs', () => {
    const body: AuditLogListDto = { items: [], limit: 20, offset: 0, total: 0 }
    return HttpResponse.json(body)
  }),
]

export const errorAuditLogHandlers = [
  http.get('*/api/v1/audit-logs', () =>
    HttpResponse.json(
      {
        type: 'https://nene-payout.dev/problems/internal-server-error',
        title: 'Server Error',
        status: 500,
      },
      { status: 500 },
    ),
  ),
]
