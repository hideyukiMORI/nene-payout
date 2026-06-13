import { EmptyState, ErrorState, PageHeader, Spinner, Text } from '@/shared/ui'
import { useTranslation } from '@/shared/i18n'
import type { VendorsPageState } from '../hooks/use-vendors-page'

export interface VendorListViewProps {
  state: VendorsPageState
}

export function VendorListView({ state }: VendorListViewProps) {
  const { t } = useTranslation()

  return (
    <section className="px-inline-md">
      <PageHeader title={t('admin.vendors.pageTitle')} />
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
            <li key={vendor.id} className="border-b border-border py-stack-sm">
              <Text>{vendor.name}</Text>
              <Text tone="muted">
                {vendor.bankCode}-{vendor.branchCode} {vendor.accountNumber}
              </Text>
            </li>
          ))}
        </ul>
      )
  }
}
