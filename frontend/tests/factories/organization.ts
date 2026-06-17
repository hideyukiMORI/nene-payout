import type { OrganizationDto } from '@/entities/organization/api-types'

export function organizationDto(overrides: Partial<OrganizationDto> = {}): OrganizationDto {
  return {
    id: '01ORG00000000000000000001',
    slug: 'acme',
    name: 'Acme 株式会社',
    custom_domain: 'pay.acme.example',
    is_active: true,
    created_at: '2026-06-13T00:00:00Z',
    updated_at: '2026-06-13T00:00:00Z',
    ...overrides,
  }
}
