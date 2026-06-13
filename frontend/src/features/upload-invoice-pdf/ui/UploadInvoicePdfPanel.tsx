import { useNavigate } from 'react-router-dom'
import {
  toReceivedInvoiceId,
  useAttachReceivedInvoicePdf,
  useReceivedInvoice,
} from '@/entities/received-invoice'
import { ErrorState, PageHeader, Spinner, Text } from '@/shared/ui'
import { formatJpy } from '@/shared/lib'
import { useTranslation } from '@/shared/i18n'
import { UploadInvoicePdfForm } from './UploadInvoicePdfForm'

const INVOICES_PATH = '/received-invoices'

export interface UploadInvoicePdfPanelProps {
  receivedInvoiceId: string
}

export function UploadInvoicePdfPanel({ receivedInvoiceId }: UploadInvoicePdfPanelProps) {
  const { t, locale } = useTranslation()
  const navigate = useNavigate()
  const id = toReceivedInvoiceId(receivedInvoiceId)
  const query = useReceivedInvoice(id)
  const mutation = useAttachReceivedInvoicePdf()

  return (
    <section className="px-inline-md">
      <PageHeader title={t('admin.receivedInvoices.uploadPdf')} />
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
    if (mutation.isSuccess) {
      return <Text>{t('admin.receivedInvoices.pdf.success')}</Text>
    }

    return (
      <div className="flex flex-col gap-stack-md">
        <Text tone="muted">
          {t('admin.payments.amountDue', { amount: formatJpy(query.data.amount, locale) })}
        </Text>
        <UploadInvoicePdfForm
          submitting={mutation.isPending}
          submitError={mutation.isError}
          onCancel={() => {
            void navigate(INVOICES_PATH)
          }}
          onSubmit={(file) => {
            mutation.mutate({ id, file })
          }}
        />
      </div>
    )
  }
}
