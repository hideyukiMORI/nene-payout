import type { MessageKey } from '@/shared/i18n'

/** i18n key for an organization's active/inactive status label. */
export function statusLabelKey(isActive: boolean): MessageKey {
  return isActive ? 'admin.organizations.status.active' : 'admin.organizations.status.inactive'
}
