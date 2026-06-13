import { Link } from 'react-router-dom'
import { toVendorId, useVendor } from '@/entities/vendor'
import { DetailList, ErrorState, PageHeader, Spinner } from '@/shared/ui'
import { useTranslation } from '@/shared/i18n'

const EMPTY = '—'

export interface VendorDetailViewProps {
  vendorId: string
}

export function VendorDetailView({ vendorId }: VendorDetailViewProps) {
  const { t } = useTranslation()
  const query = useVendor(toVendorId(vendorId))

  return (
    <section className="px-inline-md">
      <PageHeader
        title={t('admin.vendors.detailTitle')}
        actions={
          query.isSuccess ? (
            <Link
              to={`/vendors/${vendorId}/edit`}
              className="rounded-md bg-accent px-inline-md py-stack-sm font-sans text-body font-medium text-accent-contrast"
            >
              {t('common.actions.edit')}
            </Link>
          ) : null
        }
      />
      {renderBody()}
    </section>
  )

  function renderBody() {
    if (query.isPending) {
      return <Spinner label={t('common.state.loading')} />
    }
    if (query.isError) {
      return (
        <ErrorState
          message={t('common.state.error')}
          retryLabel={t('common.actions.retry')}
          onRetry={() => {
            void query.refetch()
          }}
        />
      )
    }
    const vendor = query.data
    return (
      <DetailList
        rows={[
          { label: t('admin.vendors.field.name'), value: vendor.name },
          { label: t('admin.vendors.field.bankCode'), value: vendor.bankCode },
          { label: t('admin.vendors.field.branchCode'), value: vendor.branchCode },
          { label: t('admin.vendors.field.accountType'), value: vendor.accountType },
          { label: t('admin.vendors.field.accountNumber'), value: vendor.accountNumber },
          { label: t('admin.vendors.field.accountName'), value: vendor.accountName },
          {
            label: t('admin.vendors.field.registrationNumber'),
            value: vendor.registrationNumber ?? EMPTY,
          },
        ]}
      />
    )
  }
}
