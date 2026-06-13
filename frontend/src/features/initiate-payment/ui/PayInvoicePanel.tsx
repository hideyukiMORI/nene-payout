import { useNavigate } from 'react-router-dom'
import { toReceivedInvoiceId, useReceivedInvoice } from '@/entities/received-invoice'
import { useInitiatePayment } from '@/entities/payment-execution'
import { EmptyState, ErrorState, PageHeader, Spinner, Text } from '@/shared/ui'
import { formatJpy } from '@/shared/lib'
import { useTranslation } from '@/shared/i18n'
import { InitiatePaymentForm } from './InitiatePaymentForm'

const INVOICES_PATH = '/received-invoices'

export interface PayInvoicePanelProps {
  receivedInvoiceId: string
}

export function PayInvoicePanel({ receivedInvoiceId }: PayInvoicePanelProps) {
  const { t, locale } = useTranslation()
  const navigate = useNavigate()
  const id = toReceivedInvoiceId(receivedInvoiceId)
  const query = useReceivedInvoice(id)
  const mutation = useInitiatePayment()

  const returnUrl = `${window.location.origin}${INVOICES_PATH}`

  return (
    <section className="px-inline-md">
      <PageHeader title={t('admin.payments.pay.title')} />
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
    if (query.data.status !== 'pending') {
      return <EmptyState message={t('admin.payments.pay.notPayable')} />
    }
    if (mutation.isSuccess && mutation.data.gatewayRedirectUrl === null) {
      return <Text>{t('admin.payments.pay.noRedirect')}</Text>
    }

    return (
      <div className="flex flex-col gap-stack-md">
        <Text tone="muted">
          {t('admin.payments.amountDue', { amount: formatJpy(query.data.amount, locale) })}
        </Text>
        <InitiatePaymentForm
          returnUrl={returnUrl}
          submitting={mutation.isPending}
          submitError={mutation.isError}
          onCancel={() => {
            void navigate(INVOICES_PATH)
          }}
          onSubmit={(input) => {
            mutation.mutate(
              { receivedInvoiceId, input },
              {
                onSuccess: (result) => {
                  if (result.gatewayRedirectUrl !== null) {
                    window.location.assign(result.gatewayRedirectUrl)
                  }
                },
              },
            )
          }}
        />
      </div>
    )
  }
}
