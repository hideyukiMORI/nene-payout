import { useNavigate } from 'react-router-dom'
import {
  toReceivedInvoiceId,
  useReceivedInvoice,
  useUpdateReceivedInvoice,
} from '@/entities/received-invoice'
import { useVendorList } from '@/entities/vendor'
import { ErrorState, PageHeader, Spinner } from '@/shared/ui'
import { useTranslation } from '@/shared/i18n'
import { invoiceToFormValues } from '../model/invoice-form'
import { InvoiceForm } from './InvoiceForm'

const INVOICES_PATH = '/received-invoices'

export interface EditInvoiceFormProps {
  receivedInvoiceId: string
}

export function EditInvoiceForm({ receivedInvoiceId }: EditInvoiceFormProps) {
  const { t } = useTranslation()
  const navigate = useNavigate()
  const id = toReceivedInvoiceId(receivedInvoiceId)
  const invoiceQuery = useReceivedInvoice(id)
  const vendorsQuery = useVendorList({ limit: 100, offset: 0, q: null })
  const mutation = useUpdateReceivedInvoice()

  return (
    <section className="px-inline-md">
      <PageHeader title={t('admin.receivedInvoices.editTitle')} />
      {renderBody()}
    </section>
  )

  function renderBody() {
    if (invoiceQuery.isPending || vendorsQuery.isPending) {
      return <Spinner label={t('common.state.loading')} />
    }
    if (invoiceQuery.isError || vendorsQuery.isError) {
      return (
        <ErrorState
          message={t('common.state.error')}
          retryLabel={t('common.actions.retry')}
          onRetry={() => {
            void invoiceQuery.refetch()
            void vendorsQuery.refetch()
          }}
        />
      )
    }
    return (
      <InvoiceForm
        vendors={vendorsQuery.data.items.map((vendor) => ({ id: vendor.id, name: vendor.name }))}
        defaultValues={invoiceToFormValues(invoiceQuery.data)}
        submitLabel={t('common.actions.save')}
        submitting={mutation.isPending}
        submitError={mutation.isError}
        onCancel={() => {
          void navigate(INVOICES_PATH)
        }}
        onSubmit={(input) => {
          mutation.mutate(
            { id, input },
            {
              onSuccess: () => {
                void navigate(INVOICES_PATH)
              },
            },
          )
        }}
      />
    )
  }
}
