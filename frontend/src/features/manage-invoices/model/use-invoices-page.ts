import { useReceivedInvoiceList, type ReceivedInvoice } from '@/entities/received-invoice'

export type InvoicesPageState =
  | { status: 'loading' }
  | { status: 'error'; retry: () => void }
  | { status: 'empty' }
  | { status: 'success'; invoices: ReceivedInvoice[] }

/**
 * Composes the received-invoice list query into an explicit, narrowed view model
 * (loading / empty / error / success) for the presentational view.
 */
export function useInvoicesPage(): InvoicesPageState {
  const query = useReceivedInvoiceList()

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

  return { status: 'success', invoices: query.data.items }
}
