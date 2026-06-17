import type { UserRole, UserStatus } from '@/entities/user'
import type { MessageKey } from '@/shared/i18n'

/** i18n keys for role labels (shared common.role.* catalog). */
export const ROLE_LABEL_KEY: Record<UserRole, MessageKey> = {
  superadmin: 'common.role.superadmin',
  admin: 'common.role.admin',
  operator: 'common.role.operator',
}

/** i18n keys for user status labels. */
export const STATUS_LABEL_KEY: Record<UserStatus, MessageKey> = {
  active: 'admin.users.status.active',
  invited: 'admin.users.status.invited',
  deactivated: 'admin.users.status.deactivated',
}
