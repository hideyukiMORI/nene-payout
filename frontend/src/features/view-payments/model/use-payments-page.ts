import { usePaymentExecutionList, type PaymentExecution } from '@/entities/payment-execution'

export type PaymentsPageState =
  | { status: 'loading' }
  | { status: 'error'; retry: () => void }
  | { status: 'empty' }
  | { status: 'success'; payments: PaymentExecution[] }

/**
 * Composes the payment-execution list query into an explicit, narrowed view
 * model (loading / empty / error / success) for the presentational view.
 */
export function usePaymentsPage(): PaymentsPageState {
  const query = usePaymentExecutionList()

  if (query.isPending) {
    return { status: 'loading' }
  }

  if (query.isError) {
    return {
      status: 'error',
      retry: () => {
        void query.refetch()
      },
    }
  }

  if (query.data.items.length === 0) {
    return { status: 'empty' }
  }

  return { status: 'success', payments: query.data.items }
}
