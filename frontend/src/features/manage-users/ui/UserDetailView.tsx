import { Link, useNavigate } from 'react-router-dom'
import { toUserId, useDeactivateUser, useUser } from '@/entities/user'
import { Button, DetailList, ErrorState, PageHeader, Spinner, Text } from '@/shared/ui'
import { useTranslation } from '@/shared/i18n'
import { ROLE_LABEL_KEY, STATUS_LABEL_KEY } from '../model/labels'

const USERS_PATH = '/users'

export interface UserDetailViewProps {
  userId: string
}

export function UserDetailView({ userId }: UserDetailViewProps) {
  const { t } = useTranslation()
  const navigate = useNavigate()
  const id = toUserId(userId)
  const query = useUser(id)
  const deactivate = useDeactivateUser()

  return (
    <section className="px-x-inline-md">
      <PageHeader
        title={t('admin.users.detailTitle')}
        actions={
          query.isSuccess ? (
            <Link
              to={`/users/${userId}/edit`}
              className="rounded-x-md bg-accent px-x-inline-md py-x-stack-sm font-sans text-body font-medium text-on-accent"
            >
              {t('common.actions.edit')}
            </Link>
          ) : null
        }
      />
      {query.isPending ? (
        <Spinner label={t('common.state.loading')} />
      ) : query.isError ? (
        <ErrorState
          message={t('common.state.error')}
          retryLabel={t('common.actions.retry')}
          onRetry={() => {
            void query.refetch()
          }}
        />
      ) : (
        <div className="flex flex-col gap-x-stack-md">
          <DetailList
            rows={[
              { label: t('admin.users.field.email'), value: query.data.email },
              { label: t('admin.users.field.role'), value: t(ROLE_LABEL_KEY[query.data.role]) },
              {
                label: t('admin.users.field.status'),
                value: t(STATUS_LABEL_KEY[query.data.status]),
              },
            ]}
          />
          {deactivate.isError ? (
            <Text tone="muted">
              <span role="alert" className="text-danger">
                {t('admin.users.deactivate.failed')}
              </span>
            </Text>
          ) : null}
          <div>
            <Button
              variant="danger"
              disabled={deactivate.isPending}
              onClick={() => {
                if (!window.confirm(t('admin.users.deactivate.confirm'))) {
                  return
                }
                deactivate.mutate(id, {
                  onSuccess: () => {
                    void navigate(USERS_PATH)
                  },
                })
              }}
            >
              {deactivate.isPending ? t('common.actions.saving') : t('common.actions.deactivate')}
            </Button>
          </div>
        </div>
      )}
    </section>
  )
}
