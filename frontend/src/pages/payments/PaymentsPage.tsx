import { PaymentListView, usePaymentsPage } from '@/features/view-payments'

export function PaymentsPage() {
  const state = usePaymentsPage()

  return <PaymentListView state={state} />
}
