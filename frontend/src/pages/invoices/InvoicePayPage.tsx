import { Navigate, useParams } from 'react-router-dom'
import { PayInvoicePanel } from '@/features/initiate-payment'

export function InvoicePayPage() {
  const { receivedInvoiceId } = useParams<{ receivedInvoiceId: string }>()

  if (receivedInvoiceId === undefined) {
    return <Navigate to="/received-invoices" replace />
  }

  return <PayInvoicePanel receivedInvoiceId={receivedInvoiceId} />
}
