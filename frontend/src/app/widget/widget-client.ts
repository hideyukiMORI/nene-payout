/**
 * Minimal API client for the embeddable widget surface. Unlike the admin
 * `apiClient` (Bearer), it authenticates with the `X-Widget-Token` carried in
 * the iframe URL and targets the `/api/v1/widget/*` namespace (ADR 0021).
 */

const BASE = '/api/v1/widget'

export interface WidgetContext {
  organization_id: string
  organization_name: string | null
  locale: string
  capabilities: string[]
}

export interface WidgetInvoice {
  id: string
  vendor_id: string
  amount: number
  due_date: string
  status: string
}

export interface WidgetInvoiceList {
  items: WidgetInvoice[]
  total?: number
}

export interface QuickPayResult {
  received_invoice: WidgetInvoice
  gateway_redirect_url: string | null
}

export interface PaymentResult {
  gateway_redirect_url: string | null
}

function widgetToken(): string {
  return new URLSearchParams(window.location.search).get('token') ?? ''
}

async function call<T>(method: string, path: string, body?: unknown): Promise<T> {
  const headers: Record<string, string> = { 'X-Widget-Token': widgetToken() }
  if (body !== undefined) {
    headers['Content-Type'] = 'application/json'
  }

  const init: RequestInit = { method, headers }
  if (body !== undefined) {
    init.body = JSON.stringify(body)
  }

  const response = await fetch(`${BASE}${path}`, init)

  if (!response.ok) {
    let detail = `Request failed (${response.status}).`
    try {
      const problem = (await response.json()) as { detail?: string }
      if (typeof problem.detail === 'string') {
        detail = problem.detail
      }
    } catch {
      // Non-JSON error body — keep the default message.
    }
    throw new Error(detail)
  }

  if (response.status === 204) {
    return undefined as T
  }

  return (await response.json()) as T
}

export const widgetApi = {
  getContext: (): Promise<WidgetContext> => call('GET', '/context'),
  listInvoices: (): Promise<WidgetInvoiceList> => call('GET', '/received-invoices?limit=50'),
  quickPay: (payload: Record<string, unknown>): Promise<QuickPayResult> =>
    call('POST', '/quick-payments', payload),
  payInvoice: (id: string): Promise<PaymentResult> =>
    call('POST', `/received-invoices/${id}/payments`, { gateway: 'stripe' }),
}

/** Formats integer minimum-currency-units as JPY (e.g. 330000 → ¥330,000). */
export function formatJpy(amount: number): string {
  return `¥${amount.toLocaleString('ja-JP')}`
}

/** Notifies the embedding host page (the loader relays to its callbacks/events). */
export function notifyHost(
  type: 'ready' | 'success' | 'failure' | 'close',
  detail?: unknown,
): void {
  window.parent.postMessage({ type: `nenepayout:${type}`, detail }, '*')
}
