import { Link } from 'react-router-dom'
import type { PaymentExecutionStatus } from '@/entities/payment-execution'
import { EmptyState, ErrorState, PageHeader, Spinner, Text } from '@/shared/ui'
import { formatJpy } from '@/shared/lib'
import { useTranslation, type MessageKey } from '@/shared/i18n'
import type { PaymentsPageState } from '../hooks/use-payments-page'

const STATUS_LABEL_KEY: Record<PaymentExecutionStatus, MessageKey> = {
  initiated: 'admin.payments.status.initiated',
  succeeded: 'admin.payments.status.succeeded',
  failed: 'admin.payments.status.failed',
  refunded: 'admin.payments.status.refunded',
  charged_back: 'admin.payments.status.chargedBack',
}

export interface PaymentListViewProps {
  state: PaymentsPageState
}

export function PaymentListView({ state }: PaymentListViewProps) {
  const { t } = useTranslation()

  return (
    <section className="px-x-inline-md">
      <PageHeader title={t('admin.payments.pageTitle')} />
      <PaymentListBody state={state} />
    </section>
  )
}

function PaymentListBody({ state }: PaymentListViewProps) {
  const { t, locale } = useTranslation()

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
      return <EmptyState message={t('admin.payments.empty')} />
    case 'success':
      return (
        <ul>
          {state.payments.map((payment) => (
            <li key={payment.id} className="border-b border-border py-x-stack-sm">
              <Link to={`/payments/${payment.id}`} className="font-sans font-medium text-accent">
                {formatJpy(payment.amount, locale)}
              </Link>
              <Text tone="muted">
                {t('admin.payments.field.gateway')}: {payment.gateway} ·{' '}
                {t(STATUS_LABEL_KEY[payment.status])}
                {payment.chargeAmount !== null
                  ? ` · ${t('admin.payments.field.chargeAmount')}: ${formatJpy(payment.chargeAmount, locale)}`
                  : ''}
              </Text>
            </li>
          ))}
        </ul>
      )
  }
}
