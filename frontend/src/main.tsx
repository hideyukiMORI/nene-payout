import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { App } from '@/App'
import { WidgetApp } from '@/app/widget/WidgetApp'
import '@/shared/ui/theme/index.css'

const root = document.getElementById('root')

if (root === null) {
  throw new Error('Root element #root not found.')
}

// The embeddable widget is a separate, standalone surface served at /widget
// (loaded by widget.js in an iframe); every other path — including the dev-only
// /widget-demo host page — is the admin app.
const isWidget = window.location.pathname === '/widget'

createRoot(root).render(<StrictMode>{isWidget ? <WidgetApp /> : <App />}</StrictMode>)
