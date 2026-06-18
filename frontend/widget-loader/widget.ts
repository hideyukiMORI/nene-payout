/**
 * NeNe Payout embeddable loader (`widget.js`).
 *
 * A single `<script>` tag drops the payment widget into the operator's own
 * (authenticated) system — no integration code required. It exposes a small
 * host API and declarative `data-*` triggers, opening the token-gated `/widget`
 * page inside an isolated modal iframe so host CSS never leaks in (ADR 0021).
 *
 *   <script src="https://HOST/assets/widget.js"
 *           data-payout-token="<JWT>" data-payout-mode="modal" async></script>
 *
 * Host integration:
 *   // Mode A — host passes the invoice + full payee bank account, then pays:
 *   NenePayout.payInvoice({ amount, due_date, vendor: { name, bank_code, branch_code,
 *                           account_type, account_number, account_name } })
 *   // or, when the payee is already a registered vendor:
 *   NenePayout.payInvoice({ amount, due_date, vendor_id })
 *   // Pay an invoice already registered in Payout, by id (account is on record):
 *   NenePayout.payRegisteredInvoice('01ABC…')
 *   // Mode B — open the embedded management list:
 *   NenePayout.open()
 *   NenePayout.on('success', (detail) => { ... })
 *
 * Declarative triggers on the host page:
 *   <button data-payout-open>請求書管理を開く</button>
 *   <button data-payout-invoice="01ABC…">この請求書を支払う</button>
 */

type PayoutEvent = 'success' | 'failure' | 'close'
type PayoutPayload = Record<string, unknown>

interface PayoutApi {
  open(): void
  payInvoice(payload: PayoutPayload): void
  payRegisteredInvoice(invoiceId: string): void
  close(): void
  on(event: PayoutEvent, callback: (detail: unknown) => void): void
}

interface FrameMessage {
  type?: string
  detail?: unknown
}

;(function bootstrap(): void {
  const script = document.currentScript as HTMLScriptElement | null
  const origin = script ? new URL(script.src).origin : window.location.origin
  const token = script?.getAttribute('data-payout-token') ?? ''

  const listeners: Record<PayoutEvent, Array<(detail: unknown) => void>> = {
    success: [],
    failure: [],
    close: [],
  }

  let overlay: HTMLDivElement | null = null
  let frame: HTMLIFrameElement | null = null
  let pendingPayload: PayoutPayload | null = null

  function widgetUrl(params: Record<string, string>): string {
    const url = new URL(`${origin}/widget`)
    url.searchParams.set('token', token)
    for (const [key, value] of Object.entries(params)) {
      url.searchParams.set(key, value)
    }
    return url.toString()
  }

  function emit(event: PayoutEvent, detail: unknown): void {
    listeners[event].forEach((callback) => callback(detail))
    window.dispatchEvent(new CustomEvent(`nenepayout:${event}`, { detail }))
  }

  function close(): void {
    if (overlay !== null) {
      overlay.remove()
      overlay = null
      frame = null
      pendingPayload = null
      emit('close', null)
    }
  }

  function openFrame(params: Record<string, string>, payload: PayoutPayload | null): void {
    close()
    pendingPayload = payload

    overlay = document.createElement('div')
    overlay.setAttribute(
      'style',
      'position:fixed;inset:0;z-index:2147483647;background:rgba(15,23,42,0.55);display:flex;align-items:center;justify-content:center;',
    )
    overlay.addEventListener('click', (event) => {
      if (event.target === overlay) {
        close()
      }
    })

    frame = document.createElement('iframe')
    frame.src = widgetUrl(params)
    frame.setAttribute('title', 'NeNe Payout')
    frame.setAttribute(
      'style',
      'width:min(480px,100%);height:min(680px,92vh);border:0;border-radius:12px;background:#fff;box-shadow:0 12px 48px rgba(0,0,0,0.32);',
    )

    overlay.appendChild(frame)
    document.body.appendChild(overlay)
  }

  window.addEventListener('message', (event: MessageEvent) => {
    if (frame === null || event.source !== frame.contentWindow) {
      return
    }

    const data = event.data as FrameMessage | null
    if (data === null || typeof data.type !== 'string') {
      return
    }

    if (data.type === 'nenepayout:ready') {
      if (pendingPayload !== null && frame.contentWindow !== null) {
        frame.contentWindow.postMessage(
          { type: 'nenepayout:payload', payload: pendingPayload },
          origin,
        )
      }
    } else if (data.type === 'nenepayout:success') {
      emit('success', data.detail)
    } else if (data.type === 'nenepayout:failure') {
      emit('failure', data.detail)
    } else if (data.type === 'nenepayout:close') {
      close()
    }
  })

  const api: PayoutApi = {
    open(): void {
      openFrame({ mode: 'manage' }, null)
    },
    payInvoice(payload: PayoutPayload): void {
      openFrame({ mode: 'quickpay' }, payload)
    },
    payRegisteredInvoice(invoiceId: string): void {
      openFrame({ mode: 'pay', invoice: invoiceId }, null)
    },
    close,
    on(event: PayoutEvent, callback: (detail: unknown) => void): void {
      if (event in listeners) {
        listeners[event].push(callback)
      }
    },
  }

  ;(window as unknown as { NenePayout: PayoutApi }).NenePayout = api

  // Declarative triggers on the host page.
  document.addEventListener('click', (event) => {
    const target = event.target as HTMLElement | null
    const trigger = target?.closest(
      '[data-payout-open],[data-payout-invoice]',
    ) as HTMLElement | null
    if (trigger === null) {
      return
    }

    event.preventDefault()
    const invoiceId = trigger.getAttribute('data-payout-invoice')
    if (invoiceId !== null && invoiceId !== '') {
      api.payRegisteredInvoice(invoiceId)
    } else {
      api.open()
    }
  })
})()
