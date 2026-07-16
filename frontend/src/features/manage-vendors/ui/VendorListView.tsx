import { Link } from 'react-router-dom'
import { EmptyState, ErrorState, PageHeader, Spinner, Text } from '@/shared/ui'
import { useTranslation } from '@/shared/i18n'
import type { VendorsPageState } from '../model/use-vendors-page'

export interface VendorListViewProps {
  state: VendorsPageState
}

export function VendorListView({ state }: VendorListViewProps) {
  const { t } = useTranslation()

  return (
    <section className="px-x-inline-md">
      <PageHeader
        title={t('admin.vendors.pageTitle')}
        actions={
          <Link
            to="/vendors/new"
            className="rounded-x-md bg-accent px-x-inline-md py-x-stack-sm font-sans font-medium text-on-accent"
          >
            {t('admin.vendors.actions.new')}
          </Link>
        }
      />
      <VendorListBody state={state} />
    </section>
  )
}

function VendorListBody({ state }: VendorListViewProps) {
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
      return <EmptyState message={t('admin.vendors.empty')} />
    case 'success':
      return (
        <ul>
          {state.vendors.map((vendor) => (
            <li
              key={vendor.id}
              className="flex items-center justify-between border-b border-border py-x-stack-sm"
            >
              <div>
                <Link to={`/vendors/${vendor.id}`} className="font-sans font-medium text-accent">
                  {vendor.name}
                </Link>
                <Text tone="muted">
                  {vendor.bankCode}-{vendor.branchCode} {vendor.accountNumber}
                </Text>
              </div>
              <Link to={`/vendors/${vendor.id}/edit`} className="font-sans font-medium text-accent">
                {t('common.actions.edit')}
              </Link>
            </li>
          ))}
        </ul>
      )
  }
}
