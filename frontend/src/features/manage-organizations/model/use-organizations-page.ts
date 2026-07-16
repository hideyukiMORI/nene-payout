import { useOrganizationList, type Organization } from '@/entities/organization'

export type OrganizationsPageState =
  | { status: 'loading' }
  | { status: 'error'; retry: () => void }
  | { status: 'empty' }
  | { status: 'success'; organizations: Organization[] }

/**
 * Composes the cross-tenant organization list query into an explicit, narrowed
 * view model (loading / empty / error / success) for the presentational view.
 */
export function useOrganizationsPage(): OrganizationsPageState {
  const query = useOrganizationList()

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

  return { status: 'success', organizations: query.data.items }
}
