import { describe, it, expect } from 'vitest'
import type { OrganizationDto } from './api-types'
import { mapOrganizationDtoToModel, mapUpdateOrganizationNameInputToDto } from './mapper'

const dto: OrganizationDto = {
  id: '01ORG00000000000000000001',
  slug: 'acme',
  name: 'Acme 株式会社',
  custom_domain: 'pay.acme.example',
  is_active: true,
  created_at: '2026-06-13T00:00:00Z',
  updated_at: '2026-06-13T00:00:00Z',
}

describe('organization mapper', () => {
  it('maps snake_case DTO to camelCase model', () => {
    const model = mapOrganizationDtoToModel(dto)

    expect(model.id).toBe('01ORG00000000000000000001')
    expect(model.slug).toBe('acme')
    expect(model.name).toBe('Acme 株式会社')
    expect(model.customDomain).toBe('pay.acme.example')
    expect(model.isActive).toBe(true)
  })

  it('keeps a null custom domain', () => {
    const model = mapOrganizationDtoToModel({ ...dto, custom_domain: null })
    expect(model.customDomain).toBeNull()
  })

  it('maps the name update input to the patch DTO', () => {
    expect(mapUpdateOrganizationNameInputToDto({ name: 'New Name' })).toEqual({ name: 'New Name' })
  })
})
