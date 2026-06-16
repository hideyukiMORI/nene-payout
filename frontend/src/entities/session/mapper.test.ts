import { describe, expect, it } from 'vitest'
import { mapCurrentUserDtoToModel } from './mapper'

describe('mapCurrentUserDtoToModel', () => {
  it('maps the wire DTO into the domain model', () => {
    const user = mapCurrentUserDtoToModel({
      id: 'user-1',
      email: 'admin@example.com',
      role: 'admin',
      organization_id: 'org-1',
    })

    expect(user).toEqual({
      id: 'user-1',
      email: 'admin@example.com',
      role: 'admin',
      organizationId: 'org-1',
    })
  })

  it('coerces an unrecognized role to null (fails closed)', () => {
    const user = mapCurrentUserDtoToModel({
      id: 'user-1',
      email: 'x@example.com',
      role: 'wizard',
      organization_id: 'org-1',
    })

    expect(user.role).toBeNull()
  })

  it('preserves null identity fields', () => {
    const user = mapCurrentUserDtoToModel({
      id: null,
      email: null,
      role: null,
      organization_id: null,
    })

    expect(user).toEqual({ id: null, email: null, role: null, organizationId: null })
  })
})
