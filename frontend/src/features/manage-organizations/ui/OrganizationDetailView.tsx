import { Link, useNavigate } from 'react-router-dom'
import { useDeactivateOrganization, useOrganizationById } from '@/entities/organization'
import { Button, DetailList, ErrorState, PageHeader, Spinner, Text } from '@/shared/ui'
import { useTranslation } from '@/shared/i18n'
import { statusLabelKey } from '../model/labels'

const EMPTY = '—'
const ORGANIZATIONS_PATH = '/organizations'

export interface OrganizationDetailViewProps {
  organizationId: string
}

export function OrganizationDetailView({ organizationId }: OrganizationDetailViewProps) {
  const { t } = useTranslation()
  const navigate = useNavigate()
  const query = useOrganizationById(organizationId)
  const deactivate = useDeactivateOrganization()

  return (
    <section className="px-x-inline-md">
      <PageHeader
        title={t('admin.organizations.detailTitle')}
        actions={
          query.isSuccess ? (
            <Link
              to={`/organizations/${organizationId}/edit`}
              className="rounded-x-md bg-accent px-x-inline-md py-x-stack-sm font-sans font-medium text-on-accent"
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
              { label: t('admin.organizations.field.slug'), value: query.data.slug },
              { label: t('admin.organizations.field.name'), value: query.data.name },
              {
                label: t('admin.organizations.field.customDomain'),
                value: query.data.customDomain ?? EMPTY,
              },
              {
                label: t('admin.organizations.field.status'),
                value: t(statusLabelKey(query.data.isActive)),
              },
            ]}
          />
          {deactivate.isError ? (
            <Text tone="muted">
              <span role="alert" className="text-danger">
                {t('admin.organizations.deactivate.failed')}
              </span>
            </Text>
          ) : null}
          {query.data.isActive ? (
            <div>
              <Button
                variant="danger"
                disabled={deactivate.isPending}
                onClick={() => {
                  if (!window.confirm(t('admin.organizations.deactivate.confirm'))) {
                    return
                  }
                  deactivate.mutate(organizationId, {
                    onSuccess: () => {
                      void navigate(ORGANIZATIONS_PATH)
                    },
                  })
                }}
              >
                {deactivate.isPending ? t('common.actions.saving') : t('common.actions.deactivate')}
              </Button>
            </div>
          ) : null}
        </div>
      )}
    </section>
  )
}
