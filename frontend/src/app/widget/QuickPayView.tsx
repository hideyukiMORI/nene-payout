import { useMutation } from '@tanstack/react-query'
import { useEffect, useState } from 'react'
import { useTranslation } from '@/shared/i18n'
import { Button, Spinner, Text } from '@/shared/ui'
import { formatJpy, notifyHost, widgetApi } from './widget-client'

function readString(source: Record<string, unknown>, key: string): string | null {
  const value = source[key]
  return typeof value === 'string' ? value : null
}

function readNumber(source: Record<string, unknown>, key: string): number | null {
  const value = source[key]
  return typeof value === 'number' ? value : null
}

function readPayeeName(source: Record<string, unknown>): string | null {
  const vendor = source['vendor']
  if (vendor !== null && typeof vendor === 'object') {
    const name = (vendor as Record<string, unknown>)['name']
    if (typeof name === 'string') {
      return name
    }
  }
  return readString(source, 'payee_name')
}

/**
 * Mode A: the host already has the invoice and called `NenePayout.payInvoice(...)`.
 * The loader posts the payload in; we confirm the amount + payee (振込先) and pay,
 * which records the invoice on the operator's server then hands off to the
 * gateway-hosted card page.
 */
export function QuickPayView() {
  const { t } = useTranslation()
  const [payload, setPayload] = useState<Record<string, unknown> | null>(null)

  const payMutation = useMutation({
    mutationFn: (body: Record<string, unknown>) => widgetApi.quickPay(body),
    onSuccess: (result) => {
      notifyHost('success', { received_invoice_id: result.received_invoice.id })
      if (result.gateway_redirect_url !== null) {
        window.location.assign(result.gateway_redirect_url)
      }
    },
    onError: (cause) => {
      notifyHost('failure', {
        message: cause instanceof Error ? cause.message : t('widget.complete.failure'),
      })
    },
  })

  useEffect(() => {
    function onMessage(event: MessageEvent): void {
      const data = event.data as { type?: string; payload?: unknown }
      if (
        data.type === 'nenepayout:payload' &&
        data.payload !== null &&
        typeof data.payload === 'object'
      ) {
        setPayload(data.payload as Record<string, unknown>)
      }
    }

    window.addEventListener('message', onMessage)
    notifyHost('ready')
    return () => {
      window.removeEventListener('message', onMessage)
    }
  }, [])

  if (payload === null) {
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

  const amount = readNumber(payload, 'amount')
  const payeeName = readPayeeName(payload)
  const dueDate = readString(payload, 'due_date')

  return (
    <div className="min-h-screen bg-surface p-inline-md">
      <div className="mx-auto flex max-w-md flex-col gap-stack-md rounded-md border border-border bg-surface-raised p-inline-md shadow-sm">
        <h2 className="font-sans text-heading font-medium text-primary">{t('widget.pay.title')}</h2>

        <dl className="flex flex-col gap-stack-sm">
          {amount !== null && (
            <div className="flex justify-between">
              <dt className="text-muted">{t('common.field.amount')}</dt>
              <dd className="font-medium">{formatJpy(amount)}</dd>
            </div>
          )}
          {payeeName !== null && (
            <div className="flex justify-between">
              <dt className="text-muted">{t('widget.pay.payee')}</dt>
              <dd className="font-medium">{payeeName}</dd>
            </div>
          )}
          {dueDate !== null && (
            <div className="flex justify-between">
              <dt className="text-muted">{t('common.field.dueDate')}</dt>
              <dd className="font-medium">{dueDate}</dd>
            </div>
          )}
        </dl>

        {payMutation.isError && (
          <p className="font-sans text-body text-danger">
            {payMutation.error instanceof Error
              ? payMutation.error.message
              : t('widget.complete.failure')}
          </p>
        )}

        <Button
          onClick={() => {
            payMutation.mutate({ gateway: 'stripe', ...payload })
          }}
          disabled={payMutation.isPending}
        >
          {payMutation.isPending ? t('widget.pay.processing') : t('widget.pay.submit')}
        </Button>
      </div>
    </div>
  )
}
