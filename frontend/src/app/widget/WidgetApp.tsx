import { Providers } from '@/app/providers'
import { ManageView } from './ManageView'
import { QuickPayView } from './QuickPayView'

/**
 * Root of the embeddable widget surface (served at `/widget`, loaded by
 * `widget.js` inside an iframe). Standalone — no admin shell, no auth gate; it
 * authenticates with the `X-Widget-Token` in the URL. `mode=quickpay` runs the
 * host-passed payment (Mode A); anything else shows the management list (Mode B).
 */
export function WidgetApp() {
  const mode = new URLSearchParams(window.location.search).get('mode')

  return <Providers>{mode === 'quickpay' ? <QuickPayView /> : <ManageView />}</Providers>
}
