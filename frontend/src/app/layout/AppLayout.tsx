import { NavLink, Outlet } from 'react-router-dom'
import { useTranslation, type MessageKey } from '@/shared/i18n'
import { Text } from '@/shared/ui'
import { roleHasCapability, useCurrentUser, type Capability } from '@/entities/session'
import { LocaleSwitcher } from '@/features/switch-locale'
import { SignOutButton } from './sign-out-button'

interface NavItem {
  to: string
  labelKey: MessageKey
  /** Capability required to see this item; undefined means any authenticated user. */
  capability?: Capability
}

const NAV_ITEMS: NavItem[] = [
  { to: '/dashboard', labelKey: 'admin.nav.dashboard' },
  {
    to: '/received-invoices',
    labelKey: 'admin.nav.receivedInvoices',
    capability: 'RegisterInvoice',
  },
  { to: '/vendors', labelKey: 'admin.nav.vendors', capability: 'ManageVendors' },
  { to: '/payments', labelKey: 'admin.nav.payments', capability: 'ViewPayments' },
  { to: '/users', labelKey: 'admin.nav.users', capability: 'ManageOrganizationSettings' },
  { to: '/settings', labelKey: 'admin.nav.settings', capability: 'ManageOrganizationSettings' },
  { to: '/audit-logs', labelKey: 'admin.nav.auditLogs', capability: 'ManageOrganizationSettings' },
]

function navLinkClass({ isActive }: { isActive: boolean }): string {
  const base = 'block rounded-md px-inline-md py-stack-sm font-sans text-body'
  return isActive ? `${base} bg-surface-raised font-medium text-accent` : `${base} text-muted`
}

/**
 * Authenticated app shell: header (brand, locale switch, sign out) plus a
 * primary sidebar nav. Page content renders through <Outlet />.
 */
export function AppLayout() {
  const { t } = useTranslation()
  const { data: currentUser } = useCurrentUser()
  const role = currentUser?.role ?? null

  const navItems = NAV_ITEMS.filter(
    (item) => item.capability === undefined || roleHasCapability(role, item.capability),
  )

  return (
    <div className="min-h-screen bg-surface">
      <header className="flex items-center justify-between border-b border-border px-inline-md py-stack-sm">
        <Text>{t('app.name')}</Text>
        <div className="flex items-center gap-inline-sm">
          <LocaleSwitcher />
          <SignOutButton />
        </div>
      </header>
      <div className="flex">
        <nav aria-label={t('app.nav.label')} className="w-64 border-r border-border py-stack-md">
          <ul className="flex flex-col gap-inline-sm px-inline-sm">
            {navItems.map((item) => (
              <li key={item.to}>
                <NavLink to={item.to} className={navLinkClass}>
                  {t(item.labelKey)}
                </NavLink>
              </li>
            ))}
          </ul>
        </nav>
        <main className="flex-1 py-stack-md">
          <Outlet />
        </main>
      </div>
    </div>
  )
}
