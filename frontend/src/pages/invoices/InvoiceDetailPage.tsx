import { Navigate, useParams } from 'react-router-dom'
import { InvoiceDetailView } from '@/features/manage-invoices'

export function InvoiceDetailPage() {
  const { receivedInvoiceId } = useParams<{ receivedInvoiceId: string }>()

  if (receivedInvoiceId === undefined) {
    return <Navigate to="/received-invoices" replace />
  }

  return <InvoiceDetailView receivedInvoiceId={receivedInvoiceId} />
}
