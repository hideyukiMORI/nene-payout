import { Navigate, useParams } from 'react-router-dom'
import { PaymentDetailView } from '@/features/view-payments'

export function PaymentDetailPage() {
  const { paymentExecutionId } = useParams<{ paymentExecutionId: string }>()

  if (paymentExecutionId === undefined) {
    return <Navigate to="/payments" replace />
  }

  return <PaymentDetailView paymentExecutionId={paymentExecutionId} />
}
