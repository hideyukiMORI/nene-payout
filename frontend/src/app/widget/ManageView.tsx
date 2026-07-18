import { useMutation, useQuery } from '@tanstack/react-query'
import { useTranslation, type MessageKey } from '@/shared/i18n'
import { Button, Spinner, Text } from '@/shared/ui'
import { formatJpy, notifyHost, widgetApi } from './widget-client'

const STATUS_LABEL: Record<string, MessageKey> = {
  pending: 'admin.receivedInvoices.status.pending',
  processing: 'admin.receivedInvoices.status.processing',
  paid: 'admin.receivedInvoices.status.paid',
  failed: 'admin.receivedInvoices.status.failed',
  voided: 'admin.receivedInvoices.status.voided',
}

/**
 * Mode B: the host has no invoice system, so the widget renders a self-contained
 * list of the organization's received invoices and pays the pending ones by card.
 */
export function ManageView() {
  const { t } = useTranslation()

  const invoices = useQuery({
    queryKey: ['widget', 'received-invoices'],
    queryFn: () => widgetApi.listInvoices(),
  })

  const payMutation = useMutation({
    mutationFn: (id: string) => widgetApi.payInvoice(id),
    onSuccess: (result, id) => {
      notifyHost('success', { received_invoice_id: id })
      if (result.gateway_redirect_url !== null) {
        window.location.assign(result.gateway_redirect_url)
        return
      }
      void invoices.refetch()
    },
    onError: (cause) => {
      notifyHost('failure', { message: cause instanceof Error ? cause.message : 'error' })
    },
  })

  if (invoices.isPending) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-surface">
        <Spinner label={t('common.state.loading')} />
      </div>
    )
  }

  if (invoices.isError) {
    const message =
      invoices.error instanceof Error ? invoices.error.message : t('common.state.error')
    return (
      <div className="flex min-h-screen flex-col items-center justify-center gap-x-stack-sm bg-surface p-x-inline-md">
        <p className="font-sans text-danger">{message}</p>
        <Button
          variant="secondary"
          onClick={() => {
            void invoices.refetch()
          }}
        >
          {t('common.actions.retry')}
        </Button>
      </div>
    )
  }

  const items = invoices.data.items

  return (
    <div className="min-h-screen bg-surface p-x-inline-md">
      <div className="mx-auto flex max-w-md flex-col gap-x-stack-md">
        <h2 className="font-sans font-medium text-text-primary">{t('widget.manage.title')}</h2>

        {items.length === 0 ? (
          <Text>{t('widget.manage.empty')}</Text>
        ) : (
          <ul className="flex flex-col gap-x-inline-sm">
            {items.map((invoice) => (
              <li
                key={invoice.id}
                className="flex items-center justify-between rounded-x-md border border-border bg-surface-raised p-x-inline-md"
              >
                <div className="flex flex-col">
                  <span className="font-medium">{formatJpy(invoice.amount)}</span>
                  <span className="text-text-muted">
                    {invoice.due_date} ·{' '}
                    {t(STATUS_LABEL[invoice.status] ?? 'admin.receivedInvoices.status.pending')}
                  </span>
                </div>
                {invoice.status === 'pending' && (
                  <Button
                    onClick={() => {
                      payMutation.mutate(invoice.id)
                    }}
                    disabled={payMutation.isPending && payMutation.variables === invoice.id}
                  >
                    {payMutation.isPending && payMutation.variables === invoice.id
                      ? t('widget.pay.processing')
                      : t('admin.payments.initiate')}
                  </Button>
                )}
              </li>
            ))}
          </ul>
        )}
      </div>
    </div>
  )
}
