import { describe, it, expect } from 'vitest'
import type { UserDto, UserListDto } from './api-types'
import {
  mapInviteUserInputToDto,
  mapUpdateUserRoleInputToDto,
  mapUserDtoToModel,
  mapUserListDtoToModel,
} from './mapper'

const dto: UserDto = {
  id: '01USER0000000000000000001',
  email: 'admin@example.com',
  role: 'admin',
  organization_id: '01ORG00000000000000000001',
  status: 'invited',
  created_at: '2026-06-13T00:00:00Z',
  updated_at: '2026-06-13T00:00:00Z',
}

describe('user mapper', () => {
  it('maps snake_case DTO to camelCase model', () => {
    const model = mapUserDtoToModel(dto)

    expect(model.id).toBe('01USER0000000000000000001')
    expect(model.organizationId).toBe('01ORG00000000000000000001')
    expect(model.email).toBe('admin@example.com')
    expect(model.role).toBe('admin')
    expect(model.status).toBe('invited')
  })

  it('keeps a null organization id (superadmin)', () => {
    const model = mapUserDtoToModel({ ...dto, organization_id: null })
    expect(model.organizationId).toBeNull()
  })

  it('fails closed to operator for an unrecognized role', () => {
    const model = mapUserDtoToModel({ ...dto, role: 'wizard' })
    expect(model.role).toBe('operator')
  })

  it('falls back to active for an unrecognized status', () => {
    const model = mapUserDtoToModel({ ...dto, status: 'zombie' })
    expect(model.status).toBe('active')
  })

  it('maps the list envelope and defaults total to null when absent', () => {
    const listDto: UserListDto = { items: [dto], limit: 20, offset: 0 }
    const list = mapUserListDtoToModel(listDto)

    expect(list.items).toHaveLength(1)
    expect(list.total).toBeNull()
  })

  it('maps the list total when present', () => {
    const list = mapUserListDtoToModel({ items: [dto], limit: 20, offset: 0, total: 1 })
    expect(list.total).toBe(1)
  })

  it('maps invite input to the create DTO', () => {
    expect(mapInviteUserInputToDto({ email: 'new@example.com', role: 'operator' })).toEqual({
      email: 'new@example.com',
      role: 'operator',
    })
  })

  it('maps update-role input to the patch DTO', () => {
    expect(mapUpdateUserRoleInputToDto({ role: 'admin' })).toEqual({ role: 'admin' })
  })
})
