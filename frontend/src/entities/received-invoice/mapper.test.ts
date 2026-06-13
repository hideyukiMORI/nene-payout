import { describe, it, expect } from 'vitest'
import type { ReceivedInvoiceDto, ReceivedInvoiceListDto } from './api-types'
import {
  mapCreateReceivedInvoiceInputToDto,
  mapReceivedInvoiceDtoToModel,
  mapReceivedInvoiceListDtoToModel,
} from './mapper'
import type { CreateReceivedInvoiceInput } from './model'

const dto: ReceivedInvoiceDto = {
  id: '01INV0000000000000000000001',
  organization_id: '01ORG00000000000000000001',
  vendor_id: '01VENDOR000000000000000001',
  amount: 110000,
  due_date: '2026-07-31',
  status: 'pending',
  registration_number: 'T1234567890123',
  tax_breakdown: [{ tax_rate_bps: 1000, taxable_amount: 100000, tax_amount: 10000 }],
  vault_document_url: 'https://vault.example/doc/1',
  created_at: '2026-06-14T00:00:00Z',
  updated_at: '2026-06-14T00:00:00Z',
}

describe('received invoice mapper', () => {
  it('maps snake_case DTO to camelCase model', () => {
    const model = mapReceivedInvoiceDtoToModel(dto)

    expect(model.id).toBe('01INV0000000000000000000001')
    expect(model.vendorId).toBe('01VENDOR000000000000000001')
    expect(model.amount).toBe(110000)
    expect(model.dueDate).toBe('2026-07-31')
    expect(model.status).toBe('pending')
    expect(model.taxBreakdown).toEqual([
      { taxRateBps: 1000, taxableAmount: 100000, taxAmount: 10000 },
    ])
    expect(model.vaultDocumentUrl).toBe('https://vault.example/doc/1')
  })

  it('defaults optional fields when absent', () => {
    const minimal: ReceivedInvoiceDto = {
      id: '01INV0000000000000000000002',
      organization_id: '01ORG00000000000000000001',
      vendor_id: '01VENDOR000000000000000001',
      amount: 5000,
      due_date: '2026-08-01',
      status: 'paid',
      created_at: '2026-06-14T00:00:00Z',
      updated_at: '2026-06-14T00:00:00Z',
    }
    const model = mapReceivedInvoiceDtoToModel(minimal)

    expect(model.registrationNumber).toBeNull()
    expect(model.taxBreakdown).toEqual([])
    expect(model.vaultDocumentUrl).toBeNull()
  })

  it('maps the list envelope and defaults total to null when absent', () => {
    const listDto: ReceivedInvoiceListDto = { items: [dto], limit: 20, offset: 0 }
    const list = mapReceivedInvoiceListDtoToModel(listDto)

    expect(list.items).toHaveLength(1)
    expect(list.total).toBeNull()
  })

  it('maps the list total when present', () => {
    const list = mapReceivedInvoiceListDtoToModel({ items: [dto], limit: 20, offset: 0, total: 1 })
    expect(list.total).toBe(1)
  })

  it('maps create input to snake_case DTO', () => {
    const input: CreateReceivedInvoiceInput = {
      vendorId: '01VENDOR000000000000000001',
      amount: 8800,
      dueDate: '2026-09-30',
      registrationNumber: null,
      taxBreakdown: [{ taxRateBps: 800, taxableAmount: 8000, taxAmount: 800 }],
      vaultDocumentUrl: null,
    }

    expect(mapCreateReceivedInvoiceInputToDto(input)).toEqual({
      vendor_id: '01VENDOR000000000000000001',
      amount: 8800,
      due_date: '2026-09-30',
      registration_number: null,
      tax_breakdown: [{ tax_rate_bps: 800, taxable_amount: 8000, tax_amount: 800 }],
      vault_document_url: null,
    })
  })
})
