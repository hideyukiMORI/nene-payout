import { useMutation, useQuery } from '@tanstack/react-query'
import { useEffect, useState } from 'react'
import { useTranslation } from '@/shared/i18n'
import { Button, Spinner, Text } from '@/shared/ui'
import { formatJpy, maskAccount, notifyHost, widgetApi, type WidgetVendor } from './widget-client'

function readString(source: Record<string, unknown>, key: string): string | null {
  const value = source[key]
  return typeof value === 'string' ? value : null
}

function readNumber(source: Record<string, unknown>, key: string): number | null {
  const value = source[key]
  return typeof value === 'number' ? value : null
}

const VENDOR_FIELDS = [
  'name',
  'bank_code',
  'branch_code',
  'account_type',
  'account_number',
  'account_name',
] as const

/** Extracts a full inline payee account from the host payload, if present. */
function readInlineVendor(source: Record<string, unknown>): WidgetVendor | null {
  const vendor = source['vendor']
  if (vendor === null || typeof vendor !== 'object') {
    return null
  }
  const record = vendor as Record<string, unknown>
  if (!VENDOR_FIELDS.every((field) => typeof record[field] === 'string')) {
    return null
  }
  return {
    id: '',
    name: record['name'] as string,
    bank_code: record['bank_code'] as string,
    branch_code: record['branch_code'] as string,
    account_type: record['account_type'] as string,
    account_number: record['account_number'] as string,
    account_name: record['account_name'] as string,
  }
}

/**
 * Mode A: the host already has the invoice and called `NenePayout.payInvoice(...)`.
 * The loader posts the payload in; we confirm the amount and the **payee bank
 * account** (振込先口座 — a name alone is never enough to transfer), then pay,
 * which records the invoice on the operator's server and hands off to the
 * gateway-hosted card page.
 */
export function QuickPayView() {
  const { t } = useTranslation()
  const [payload, setPayload] = useState<Record<string, unknown> | null>(null)

  const inlineVendor = payload !== null ? readInlineVendor(payload) : null
  const vendorId = payload !== null ? readString(payload, 'vendor_id') : null

  const vendorQuery = useQuery({
    queryKey: ['widget', 'vendor', vendorId],
    queryFn: () => widgetApi.getVendor(vendorId ?? ''),
    enabled: inlineVendor === null && vendorId !== null,
  })

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
  const dueDate = readString(payload, 'due_date')
  const vendor = inlineVendor ?? vendorQuery.data ?? null
  const payeeName = vendor?.name ?? readString(payload, 'payee_name')

  return (
    <div className="min-h-screen bg-surface p-inline-md">
      <div className="mx-auto flex max-w-md flex-col gap-stack-md rounded-md border border-border bg-surface-raised p-inline-md shadow-sm">
        <h2 className="font-sans text-heading font-medium text-primary">{t('widget.pay.title')}</h2>

        <dl className="flex flex-col gap-stack-sm">
          {amount !== null && (
            <div className="flex justify-between gap-inline-md">
              <dt className="text-muted">{t('common.field.amount')}</dt>
              <dd className="font-medium">{formatJpy(amount)}</dd>
            </div>
          )}
          {payeeName !== null && (
            <div className="flex justify-between gap-inline-md">
              <dt className="text-muted">{t('widget.pay.payee')}</dt>
              <dd className="font-medium">{payeeName}</dd>
            </div>
          )}
          {vendor !== null && (
            <div className="flex justify-between gap-inline-md">
              <dt className="shrink-0 text-muted">{t('widget.pay.account')}</dt>
              <dd className="text-right font-medium">{maskAccount(vendor)}</dd>
            </div>
          )}
          {dueDate !== null && (
            <div className="flex justify-between gap-inline-md">
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
