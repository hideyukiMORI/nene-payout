import { describe, it, expect } from 'vitest'
import type { VendorDto, VendorListDto } from './api-types'
import { mapCreateVendorInputToDto, mapVendorDtoToModel, mapVendorListDtoToModel } from './mapper'
import type { CreateVendorInput } from './model'

const dto: VendorDto = {
  id: '01VENDOR000000000000000001',
  organization_id: '01ORG00000000000000000001',
  name: '仕入先株式会社',
  bank_code: '0001',
  branch_code: '001',
  account_type: '普通',
  account_number: '1234567',
  account_name: 'シイレサキ',
  registration_number: 'T1234567890123',
  created_at: '2026-06-13T00:00:00Z',
  updated_at: '2026-06-13T00:00:00Z',
}

describe('vendor mapper', () => {
  it('maps snake_case DTO to camelCase model', () => {
    const model = mapVendorDtoToModel(dto)

    expect(model.id).toBe('01VENDOR000000000000000001')
    expect(model.organizationId).toBe('01ORG00000000000000000001')
    expect(model.bankCode).toBe('0001')
    expect(model.accountName).toBe('シイレサキ')
    expect(model.registrationNumber).toBe('T1234567890123')
  })

  it('keeps a null registration number', () => {
    const model = mapVendorDtoToModel({ ...dto, registration_number: null })
    expect(model.registrationNumber).toBeNull()
  })

  it('maps the list envelope and defaults total to null when absent', () => {
    const listDto: VendorListDto = { items: [dto], limit: 20, offset: 0 }
    const list = mapVendorListDtoToModel(listDto)

    expect(list.items).toHaveLength(1)
    expect(list.total).toBeNull()
  })

  it('maps the list total when present', () => {
    const list = mapVendorListDtoToModel({ items: [dto], limit: 20, offset: 0, total: 1 })
    expect(list.total).toBe(1)
  })

  it('maps create input to snake_case DTO', () => {
    const input: CreateVendorInput = {
      name: 'Acme',
      bankCode: '0001',
      branchCode: '001',
      accountType: '当座',
      accountNumber: '7654321',
      accountName: 'アクメ',
      registrationNumber: null,
    }

    expect(mapCreateVendorInputToDto(input)).toEqual({
      name: 'Acme',
      bank_code: '0001',
      branch_code: '001',
      account_type: '当座',
      account_number: '7654321',
      account_name: 'アクメ',
      registration_number: null,
    })
  })
})
