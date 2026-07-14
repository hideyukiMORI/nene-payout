import {
  toPaymentExecutionId,
  usePaymentExecution,
  type PaymentExecutionStatus,
} from '@/entities/payment-execution'
import { DetailList, ErrorState, PageHeader, Spinner } from '@/shared/ui'
import { formatDateTime, formatJpy } from '@/shared/lib'
import { useTranslation, type MessageKey } from '@/shared/i18n'

const EMPTY = '—'

const STATUS_LABEL_KEY: Record<PaymentExecutionStatus, MessageKey> = {
  initiated: 'admin.payments.status.initiated',
  succeeded: 'admin.payments.status.succeeded',
  failed: 'admin.payments.status.failed',
  refunded: 'admin.payments.status.refunded',
  charged_back: 'admin.payments.status.chargedBack',
}

export interface PaymentDetailViewProps {
  paymentExecutionId: string
}

export function PaymentDetailView({ paymentExecutionId }: PaymentDetailViewProps) {
  const { t, locale } = useTranslation()
  const query = usePaymentExecution(toPaymentExecutionId(paymentExecutionId))

  return (
    <section className="px-x-inline-md">
      <PageHeader title={t('admin.payments.detailTitle')} />
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
    const payment = query.data
    return (
      <DetailList
        rows={[
          { label: t('common.field.amount'), value: formatJpy(payment.amount, locale) },
          {
            label: t('admin.payments.field.chargeAmount'),
            value: payment.chargeAmount === null ? EMPTY : formatJpy(payment.chargeAmount, locale),
          },
          {
            label: t('admin.payments.field.processingFee'),
            value:
              payment.processingFee === null ? EMPTY : formatJpy(payment.processingFee, locale),
          },
          { label: t('admin.payments.field.gateway'), value: payment.gateway },
          {
            label: t('admin.payments.field.gatewayReference'),
            value: payment.gatewayReference ?? EMPTY,
          },
          { label: t('common.field.status'), value: t(STATUS_LABEL_KEY[payment.status]) },
          {
            label: t('admin.payments.field.initiatedAt'),
            value: formatDateTime(payment.initiatedAt, locale),
          },
          {
            label: t('admin.payments.field.completedAt'),
            value:
              payment.completedAt === null ? EMPTY : formatDateTime(payment.completedAt, locale),
          },
          { label: t('admin.payments.field.receivedInvoice'), value: payment.receivedInvoiceId },
        ]}
      />
    )
  }
}
