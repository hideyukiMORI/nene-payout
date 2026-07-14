import { Link } from 'react-router-dom'
import { EmptyState, ErrorState, PageHeader, Spinner, Text } from '@/shared/ui'
import { useTranslation } from '@/shared/i18n'
import { ROLE_LABEL_KEY, STATUS_LABEL_KEY } from '../model/labels'
import type { UsersPageState } from '../hooks/use-users-page'

export interface UserListViewProps {
  state: UsersPageState
}

export function UserListView({ state }: UserListViewProps) {
  const { t } = useTranslation()

  return (
    <section className="px-x-inline-md">
      <PageHeader
        title={t('admin.users.pageTitle')}
        actions={
          <Link
            to="/users/new"
            className="rounded-x-md bg-accent px-x-inline-md py-x-stack-sm font-sans font-medium text-on-accent"
          >
            {t('admin.users.actions.invite')}
          </Link>
        }
      />
      <UserListBody state={state} />
    </section>
  )
}

function UserListBody({ state }: UserListViewProps) {
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
      return <EmptyState message={t('admin.users.empty')} />
    case 'success':
      return (
        <ul>
          {state.users.map((user) => (
            <li
              key={user.id}
              className="flex items-center justify-between border-b border-border py-x-stack-sm"
            >
              <div>
                <Link to={`/users/${user.id}`} className="font-sans font-medium text-accent">
                  {user.email}
                </Link>
                <Text tone="muted">
                  {t(ROLE_LABEL_KEY[user.role])} · {t(STATUS_LABEL_KEY[user.status])}
                </Text>
              </div>
              <Link to={`/users/${user.id}/edit`} className="font-sans font-medium text-accent">
                {t('common.actions.edit')}
              </Link>
            </li>
          ))}
        </ul>
      )
  }
}
