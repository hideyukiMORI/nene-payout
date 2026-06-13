import { Navigate, useParams } from 'react-router-dom'
import { EditInvoiceForm } from '@/features/manage-invoices'

export function InvoiceEditPage() {
  const { receivedInvoiceId } = useParams<{ receivedInvoiceId: string }>()

  if (receivedInvoiceId === undefined) {
    return <Navigate to="/received-invoices" replace />
  }

  return <EditInvoiceForm receivedInvoiceId={receivedInvoiceId} />
}
