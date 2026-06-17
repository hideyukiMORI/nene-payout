import type { UserId } from './ids'

/** User roles (mirrors src/Auth/Role.php; see also entities/session). */
export const USER_ROLES = ['superadmin', 'admin', 'operator'] as const

export type UserRole = (typeof USER_ROLES)[number]

/**
 * Roles an admin may assign through the org user-management UI. `superadmin`
 * is provisioned out of band (future /organizations API), so it is never an
 * option here — but it can still be displayed for an existing user.
 */
export const ASSIGNABLE_ROLES = ['admin', 'operator'] as const

export type AssignableRole = (typeof ASSIGNABLE_ROLES)[number]

/** User account status (mirrors docs/terms.md §4 User.status). */
export const USER_STATUSES = ['active', 'invited', 'deactivated'] as const

export type UserStatus = (typeof USER_STATUSES)[number]

export interface User {
  id: UserId
  organizationId: string | null
  email: string
  role: UserRole
  status: UserStatus
}

export interface UserList {
  items: User[]
  limit: number
  offset: number
  total: number | null
}

/** Invite (create): the API sets status=invited with no password. */
export interface InviteUserInput {
  email: string
  role: AssignableRole
}

/** Update: only the role is mutable through this API. */
export interface UpdateUserRoleInput {
  role: AssignableRole
}
