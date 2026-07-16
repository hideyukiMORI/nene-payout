import { Link } from 'react-router-dom'
import { EmptyState, ErrorState, PageHeader, Spinner, Text } from '@/shared/ui'
import { useTranslation } from '@/shared/i18n'
import { statusLabelKey } from '../model/labels'
import type { OrganizationsPageState } from '../model/use-organizations-page'

export interface OrganizationListViewProps {
  state: OrganizationsPageState
}

export function OrganizationListView({ state }: OrganizationListViewProps) {
  const { t } = useTranslation()

  return (
    <section className="px-x-inline-md">
      <PageHeader
        title={t('admin.organizations.pageTitle')}
        actions={
          <Link
            to="/organizations/new"
            className="rounded-x-md bg-accent px-x-inline-md py-x-stack-sm font-sans font-medium text-on-accent"
          >
            {t('admin.organizations.actions.new')}
          </Link>
        }
      />
      <OrganizationListBody state={state} />
    </section>
  )
}

function OrganizationListBody({ state }: OrganizationListViewProps) {
  const { t } = useTranslation()

  switch (state.status) {
    case 'loading':
      return <Spinner label={t('common.state.loading')} />
    case 'error':
      return (
        <ErrorState
          message={t('common.state.error')}
          retryLabel={t('common.actions.retry')}
          onRetry={state.retry}
        />
      )
    case 'empty':
      return <EmptyState message={t('admin.organizations.empty')} />
    case 'success':
      return (
        <ul>
          {state.organizations.map((organization) => (
            <li
              key={organization.id}
              className="flex items-center justify-between border-b border-border py-x-stack-sm"
            >
              <div>
                <Link
                  to={`/organizations/${organization.id}`}
                  className="font-sans font-medium text-accent"
                >
                  {organization.name}
                </Link>
                <Text tone="muted">
                  {organization.slug} · {t(statusLabelKey(organization.isActive))}
                </Text>
              </div>
              <Link
                to={`/organizations/${organization.id}/edit`}
                className="font-sans font-medium text-accent"
              >
                {t('common.actions.edit')}
              </Link>
            </li>
          ))}
        </ul>
      )
  }
}
