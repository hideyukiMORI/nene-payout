import { describe, it, expect } from 'vitest'
import type { OrganizationDto, OrganizationListDto } from './api-types'
import {
  mapCreateOrganizationInputToDto,
  mapOrganizationDtoToModel,
  mapOrganizationListDtoToModel,
  mapUpdateOrganizationInputToDto,
  mapUpdateOrganizationNameInputToDto,
} from './mapper'

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

  it('maps the management list envelope and defaults total to null when absent', () => {
    const listDto: OrganizationListDto = { items: [dto], limit: 20, offset: 0 }
    const list = mapOrganizationListDtoToModel(listDto)

    expect(list.items).toHaveLength(1)
    expect(list.items[0]?.slug).toBe('acme')
    expect(list.total).toBeNull()
  })

  it('maps the management list total when present', () => {
    const list = mapOrganizationListDtoToModel({ items: [dto], limit: 20, offset: 0, total: 1 })
    expect(list.total).toBe(1)
  })

  it('maps the create input to the snake_case DTO', () => {
    expect(
      mapCreateOrganizationInputToDto({ slug: 'newco', name: 'New Co.', customDomain: null }),
    ).toEqual({ slug: 'newco', name: 'New Co.', custom_domain: null })
  })

  it('maps the management update input to the snake_case DTO', () => {
    expect(
      mapUpdateOrganizationInputToDto({ name: 'Renamed', customDomain: 'pay.acme.example' }),
    ).toEqual({ name: 'Renamed', custom_domain: 'pay.acme.example' })
  })
})
