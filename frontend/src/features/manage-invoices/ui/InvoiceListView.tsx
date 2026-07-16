import { Link } from 'react-router-dom'
import type { ReceivedInvoiceStatus } from '@/entities/received-invoice'
import { EmptyState, ErrorState, PageHeader, Spinner, Text } from '@/shared/ui'
import { formatDate, formatJpy } from '@/shared/lib'
import { useTranslation, type MessageKey } from '@/shared/i18n'
import type { InvoicesPageState } from '../hooks/use-invoices-page'

const STATUS_LABEL_KEY: Record<ReceivedInvoiceStatus, MessageKey> = {
  pending: 'admin.receivedInvoices.status.pending',
  processing: 'admin.receivedInvoices.status.processing',
  paid: 'admin.receivedInvoices.status.paid',
  failed: 'admin.receivedInvoices.status.failed',
  voided: 'admin.receivedInvoices.status.voided',
}

export interface InvoiceListViewProps {
  state: InvoicesPageState
}

export function InvoiceListView({ state }: InvoiceListViewProps) {
  const { t } = useTranslation()

  return (
    <section className="px-x-inline-md">
      <PageHeader
        title={t('admin.receivedInvoices.pageTitle')}
        actions={
          <Link
            to="/received-invoices/new"
            className="rounded-x-md bg-accent px-x-inline-md py-x-stack-sm font-sans font-medium text-on-accent"
          >
            {t('admin.receivedInvoices.actions.new')}
          </Link>
        }
      />
      <InvoiceListBody state={state} />
    </section>
  )
}

function InvoiceListBody({ state }: InvoiceListViewProps) {
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
      return <EmptyState message={t('admin.receivedInvoices.empty')} />
    case 'success':
      return (
        <ul>
          {state.invoices.map((invoice) => (
            <li
              key={invoice.id}
              className="flex items-center justify-between border-b border-border py-x-stack-sm"
            >
              <div>
                <Link
                  to={`/received-invoices/${invoice.id}`}
                  className="font-sans font-medium text-accent"
                >
                  {formatJpy(invoice.amount, locale)}
                </Link>
                <Text tone="muted">
                  {t('common.field.dueDate')}: {formatDate(invoice.dueDate, locale)} ·{' '}
                  {t(STATUS_LABEL_KEY[invoice.status])}
                </Text>
              </div>
              <div className="flex items-center gap-x-inline-md">
                <Link
                  to={`/received-invoices/${invoice.id}/pdf`}
                  className="font-sans font-medium text-accent"
                >
                  {t('admin.receivedInvoices.uploadPdf')}
                </Link>
                {invoice.status === 'pending' ? (
                  <>
                    <Link
                      to={`/received-invoices/${invoice.id}/edit`}
                      className="font-sans font-medium text-accent"
                    >
                      {t('common.actions.edit')}
                    </Link>
                    <Link
                      to={`/received-invoices/${invoice.id}/pay`}
                      className="font-sans font-medium text-accent"
                    >
                      {t('admin.payments.initiate')}
                    </Link>
                  </>
                ) : null}
              </div>
            </li>
          ))}
        </ul>
      )
  }
}
