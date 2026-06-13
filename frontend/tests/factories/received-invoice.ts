import type { ReceivedInvoiceDto } from '@/entities/received-invoice/api-types'

export function receivedInvoiceDto(
  overrides: Partial<ReceivedInvoiceDto> = {},
): ReceivedInvoiceDto {
  return {
    id: '01INV0000000000000000000001',
    organization_id: '01ORG00000000000000000001',
    vendor_id: '01VENDOR000000000000000001',
    amount: 110000,
    due_date: '2026-07-31',
    status: 'pending',
    registration_number: 'T1234567890123',
    tax_breakdown: [{ tax_rate_bps: 1000, taxable_amount: 100000, tax_amount: 10000 }],
    vault_document_url: null,
    created_at: '2026-06-14T00:00:00Z',
    updated_at: '2026-06-14T00:00:00Z',
    ...overrides,
  }
}
