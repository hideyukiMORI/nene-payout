import type { CurrentUserDto } from './api-types'
import { isRole, type CurrentUser } from './model'

/** Maps the GET /auth/me wire DTO into the domain model. */
export function mapCurrentUserDtoToModel(dto: CurrentUserDto): CurrentUser {
  return {
    id: dto.id,
    email: dto.email,
    role: typeof dto.role === 'string' && isRole(dto.role) ? dto.role : null,
    organizationId: dto.organization_id,
  }
}
