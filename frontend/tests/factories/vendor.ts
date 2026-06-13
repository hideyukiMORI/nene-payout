import type { VendorDto } from '@/entities/vendor/api-types'

export function vendorDto(overrides: Partial<VendorDto> = {}): VendorDto {
  return {
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
    ...overrides,
  }
}
