import { useMutation, useQuery } from '@tanstack/react-query'
import { useTranslation, type MessageKey } from '@/shared/i18n'
import { Button, Spinner, Text } from '@/shared/ui'
import { formatJpy, maskAccount, notifyHost, widgetApi } from './widget-client'

const STATUS_LABEL: Record<string, MessageKey> = {
  pending: 'admin.receivedInvoices.status.pending',
  processing: 'admin.receivedInvoices.status.processing',
  paid: 'admin.receivedInvoices.status.paid',
  failed: 'admin.receivedInvoices.status.failed',
  voided: 'admin.receivedInvoices.status.voided',
}

/**
 * Pays an invoice already registered in Payout, addressed by id
 * (`NenePayout.payRegisteredInvoice(id)` / `data-payout-invoice`). The payee bank
 * account is on record, so the host only passes the id; we still show the masked
 * account for confirmation.
 */
export function PayInvoiceView() {
  const { t } = useTranslation()
  const invoiceId = new URLSearchParams(window.location.search).get('invoice') ?? ''

  const invoiceQuery = useQuery({
    queryKey: ['widget', 'invoice', invoiceId],
    queryFn: () => widgetApi.getInvoice(invoiceId),
    enabled: invoiceId !== '',
  })

  const vendorId = invoiceQuery.data?.vendor_id ?? null
  const vendorQuery = useQuery({
    queryKey: ['widget', 'vendor', vendorId],
    queryFn: () => widgetApi.getVendor(vendorId ?? ''),
    enabled: vendorId !== null,
  })

  const payMutation = useMutation({
    mutationFn: () => widgetApi.payInvoice(invoiceId),
    onSuccess: (result) => {
      notifyHost('success', { received_invoice_id: invoiceId })
      if (result.gateway_redirect_url !== null) {
        window.location.assign(result.gateway_redirect_url)
      }
    },
    onError: (cause) => {
      notifyHost('failure', { message: cause instanceof Error ? cause.message : 'error' })
    },
  })

  if (invoiceId === '' || invoiceQuery.isError) {
    const message =
      invoiceQuery.error instanceof Error ? invoiceQuery.error.message : t('common.state.error')
    return (
      <div className="flex min-h-screen items-center justify-center bg-surface p-x-inline-md">
        <p className="font-sans text-danger">{message}</p>
      </div>
    )
  }

  if (invoiceQuery.isPending) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-surface">
        <Spinner label={t('common.state.loading')} />
      </div>
    )
  }

  if (payMutation.isSuccess) {
    const messageKey =
      payMutation.data.gateway_redirect_url !== null
        ? 'widget.pay.redirecting'
        : 'widget.complete.success'
    return (
      <div className="flex min-h-screen items-center justify-center bg-surface">
        <Text>{t(messageKey)}</Text>
      </div>
    )
  }

  const invoice = invoiceQuery.data
  const vendor = vendorQuery.data ?? null

  return (
    <div className="min-h-screen bg-surface p-x-inline-md">
      <div className="mx-auto flex max-w-md flex-col gap-x-stack-md rounded-md border border-border bg-surface-raised p-x-inline-md shadow-sm">
        <h2 className="font-sans font-medium text-text-primary">{t('widget.pay.title')}</h2>

        <dl className="flex flex-col gap-x-stack-sm">
          <div className="flex justify-between gap-x-inline-md">
            <dt className="text-text-muted">{t('common.field.amount')}</dt>
            <dd className="font-medium">{formatJpy(invoice.amount)}</dd>
          </div>
          {vendor !== null && (
            <>
              <div className="flex justify-between gap-x-inline-md">
                <dt className="text-text-muted">{t('widget.pay.payee')}</dt>
                <dd className="font-medium">{vendor.name}</dd>
              </div>
              <div className="flex justify-between gap-x-inline-md">
                <dt className="shrink-0 text-text-muted">{t('widget.pay.account')}</dt>
                <dd className="text-right font-medium">{maskAccount(vendor)}</dd>
              </div>
            </>
          )}
          <div className="flex justify-between gap-x-inline-md">
            <dt className="text-text-muted">{t('common.field.dueDate')}</dt>
            <dd className="font-medium">{invoice.due_date}</dd>
          </div>
          <div className="flex justify-between gap-x-inline-md">
            <dt className="text-text-muted">{t('common.field.status')}</dt>
            <dd className="font-medium">
              {t(STATUS_LABEL[invoice.status] ?? 'admin.receivedInvoices.status.pending')}
            </dd>
          </div>
        </dl>

        {payMutation.isError && (
          <p className="font-sans text-danger">
            {payMutation.error instanceof Error
              ? payMutation.error.message
              : t('widget.complete.failure')}
          </p>
        )}

        {invoice.status === 'pending' && (
          <Button
            onClick={() => {
              payMutation.mutate()
            }}
            disabled={payMutation.isPending}
          >
            {payMutation.isPending ? t('widget.pay.processing') : t('widget.pay.submit')}
          </Button>
        )}
      </div>
    </div>
  )
}
