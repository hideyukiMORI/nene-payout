import { Link } from 'react-router-dom'
import {
  toReceivedInvoiceId,
  useReceivedInvoice,
  type ReceivedInvoiceStatus,
} from '@/entities/received-invoice'
import { toVendorId, useVendor } from '@/entities/vendor'
import { DetailList } from '@/shared/ui/components/DetailList'
import { ErrorState } from '@/shared/ui/components/ErrorState'
import { PageHeader } from '@/shared/ui/components/PageHeader'
import { Spinner } from '@/shared/ui/primitives/Spinner'
import { Text } from '@/shared/ui/primitives/Text'
import { formatDate, formatJpy } from '@/shared/lib'
import { useTranslation, type MessageKey } from '@/shared/i18n'

const EMPTY = '—'

const STATUS_LABEL_KEY: Record<ReceivedInvoiceStatus, MessageKey> = {
  pending: 'admin.receivedInvoices.status.pending',
  processing: 'admin.receivedInvoices.status.processing',
  paid: 'admin.receivedInvoices.status.paid',
  failed: 'admin.receivedInvoices.status.failed',
  voided: 'admin.receivedInvoices.status.voided',
}

/** Resolves a vendor id to its name, falling back to the id while loading. */
function VendorName({ vendorId }: { vendorId: string }) {
  const query = useVendor(toVendorId(vendorId))
  return <>{query.data?.name ?? vendorId}</>
}

export interface InvoiceDetailViewProps {
  receivedInvoiceId: string
}

export function InvoiceDetailView({ receivedInvoiceId }: InvoiceDetailViewProps) {
  const { t, locale } = useTranslation()
  const query = useReceivedInvoice(toReceivedInvoiceId(receivedInvoiceId))

  return (
    <section className="px-x-inline-md">
      <PageHeader
        title={t('admin.receivedInvoices.detailTitle')}
        actions={
          query.isSuccess && query.data.status === 'pending' ? (
            <Link
              to={`/received-invoices/${receivedInvoiceId}/edit`}
              className="rounded-x-md bg-accent px-x-inline-md py-x-stack-sm font-sans font-medium text-on-accent"
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
    const invoice = query.data
    return (
      <div className="flex flex-col gap-x-stack-md">
        <DetailList
          rows={[
            {
              label: t('admin.receivedInvoices.field.vendor'),
              value: <VendorName vendorId={invoice.vendorId} />,
            },
            { label: t('common.field.amount'), value: formatJpy(invoice.amount, locale) },
            { label: t('common.field.dueDate'), value: formatDate(invoice.dueDate, locale) },
            { label: t('common.field.status'), value: t(STATUS_LABEL_KEY[invoice.status]) },
            {
              label: t('admin.receivedInvoices.field.registrationNumber'),
              value: invoice.registrationNumber ?? EMPTY,
            },
            {
              label: t('admin.receivedInvoices.field.vaultDocumentUrl'),
              value: invoice.vaultDocumentUrl ?? EMPTY,
            },
          ]}
        />

        <section>
          <Text>{t('admin.receivedInvoices.taxBreakdown.title')}</Text>
          {invoice.taxBreakdown.length === 0 ? (
            <Text tone="muted">{EMPTY}</Text>
          ) : (
            <ul>
              {invoice.taxBreakdown.map((item, index) => (
                <li key={index} className="border-b border-border py-x-stack-sm">
                  <Text tone="muted">
                    {item.taxRateBps === 800
                      ? t('admin.receivedInvoices.taxBreakdown.rate8')
                      : t('admin.receivedInvoices.taxBreakdown.rate10')}{' '}
                    · {t('admin.receivedInvoices.field.taxableAmount')}:{' '}
                    {formatJpy(item.taxableAmount, locale)} ·{' '}
                    {t('admin.receivedInvoices.field.taxAmount')}:{' '}
                    {formatJpy(item.taxAmount, locale)}
                  </Text>
                </li>
              ))}
            </ul>
          )}
        </section>
      </div>
    )
  }
}
