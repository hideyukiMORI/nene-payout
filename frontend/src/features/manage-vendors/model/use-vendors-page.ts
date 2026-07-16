import { useVendorList, type Vendor } from '@/entities/vendor'

export type VendorsPageState =
  | { status: 'loading' }
  | { status: 'error'; retry: () => void }
  | { status: 'empty' }
  | { status: 'success'; vendors: Vendor[] }

/**
 * Composes the vendor list query into an explicit, narrowed view model
 * (loading / empty / error / success) for the presentational view.
 */
export function useVendorsPage(): VendorsPageState {
  const query = useVendorList()

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

  return { status: 'success', vendors: query.data.items }
}
