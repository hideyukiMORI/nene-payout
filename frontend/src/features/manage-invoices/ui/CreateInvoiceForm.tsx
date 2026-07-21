import { useNavigate } from 'react-router-dom'
import { useCreateReceivedInvoice } from '@/entities/received-invoice'
import { useVendorList } from '@/entities/vendor'
import { ErrorState } from '@/shared/ui/components/ErrorState'
import { PageHeader } from '@/shared/ui/components/PageHeader'
import { Spinner } from '@/shared/ui/primitives/Spinner'
import { useTranslation } from '@/shared/i18n'
import { InvoiceForm } from './InvoiceForm'

const INVOICES_PATH = '/received-invoices'

export function CreateInvoiceForm() {
  const { t } = useTranslation()
  const navigate = useNavigate()
  const vendorsQuery = useVendorList({ limit: 100, offset: 0, q: null })
  const mutation = useCreateReceivedInvoice()

  return (
    <section className="px-x-inline-md">
      <PageHeader title={t('admin.receivedInvoices.newTitle')} />
      {vendorsQuery.isPending ? (
        <Spinner label={t('common.state.loading')} />
      ) : vendorsQuery.isError ? (
        <ErrorState
          message={t('common.state.error')}
          retryLabel={t('common.actions.retry')}
          onRetry={() => {
            void vendorsQuery.refetch()
          }}
        />
      ) : (
        <InvoiceForm
          vendors={vendorsQuery.data.items.map((vendor) => ({ id: vendor.id, name: vendor.name }))}
          submitLabel={t('common.actions.create')}
          submitting={mutation.isPending}
          submitError={mutation.isError}
          onCancel={() => {
            void navigate(INVOICES_PATH)
          }}
          onSubmit={(input) => {
            mutation.mutate(input, {
              onSuccess: () => {
                void navigate(INVOICES_PATH)
              },
            })
          }}
        />
      )}
    </section>
  )
}
