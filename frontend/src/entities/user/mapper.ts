import type { CreateUserDto, UpdateUserDto, UserDto, UserListDto } from './api-types'
import { toUserId } from './ids'
import {
  USER_ROLES,
  USER_STATUSES,
  type InviteUserInput,
  type UpdateUserRoleInput,
  type User,
  type UserList,
  type UserRole,
  type UserStatus,
} from './model'

/** Fail closed to least privilege if the wire role is unrecognized. */
function coerceRole(value: string): UserRole {
  return (USER_ROLES as readonly string[]).includes(value) ? (value as UserRole) : 'operator'
}

function coerceStatus(value: string): UserStatus {
  return (USER_STATUSES as readonly string[]).includes(value) ? (value as UserStatus) : 'active'
}

export function mapUserDtoToModel(dto: UserDto): User {
  return {
    id: toUserId(dto.id),
    organizationId: dto.organization_id,
    email: dto.email,
    role: coerceRole(dto.role),
    status: coerceStatus(dto.status),
  }
}

export function mapUserListDtoToModel(dto: UserListDto): UserList {
  return {
    items: dto.items.map(mapUserDtoToModel),
    limit: dto.limit,
    offset: dto.offset,
    total: dto.total ?? null,
  }
}

export function mapInviteUserInputToDto(input: InviteUserInput): CreateUserDto {
  return {
    email: input.email,
    role: input.role,
  }
}

export function mapUpdateUserRoleInputToDto(input: UpdateUserRoleInput): UpdateUserDto {
  return {
    role: input.role,
  }
}
