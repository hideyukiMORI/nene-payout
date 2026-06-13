import { Navigate, useParams } from 'react-router-dom'
import { UploadInvoicePdfPanel } from '@/features/upload-invoice-pdf'

export function InvoicePdfPage() {
  const { receivedInvoiceId } = useParams<{ receivedInvoiceId: string }>()

  if (receivedInvoiceId === undefined) {
    return <Navigate to="/received-invoices" replace />
  }

  return <UploadInvoicePdfPanel receivedInvoiceId={receivedInvoiceId} />
}
