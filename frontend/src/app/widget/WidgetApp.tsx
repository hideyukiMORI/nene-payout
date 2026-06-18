import { Providers } from '@/app/providers'
import { ManageView } from './ManageView'
import { PayInvoiceView } from './PayInvoiceView'
import { QuickPayView } from './QuickPayView'

/**
 * Root of the embeddable widget surface (served at `/widget`, loaded by
 * `widget.js` inside an iframe). Standalone — no admin shell, no auth gate; it
 * authenticates with the `X-Widget-Token` in the URL.
 *
 * - `mode=quickpay` — Mode A: pay from a host-passed payload (full payee account).
 * - `mode=pay&invoice=<id>` — pay an already-registered invoice by id.
 * - otherwise — Mode B: the embedded management list.
 */
export function WidgetApp() {
  const mode = new URLSearchParams(window.location.search).get('mode')

  function view() {
    if (mode === 'quickpay') {
      return <QuickPayView />
    }
    if (mode === 'pay') {
      return <PayInvoiceView />
    }
    return <ManageView />
  }

  return <Providers>{view()}</Providers>
}
