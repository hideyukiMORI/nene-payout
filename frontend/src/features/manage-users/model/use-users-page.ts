import { useUserList, type User } from '@/entities/user'

export type UsersPageState =
  | { status: 'loading' }
  | { status: 'error'; retry: () => void }
  | { status: 'empty' }
  | { status: 'success'; users: User[] }

/**
 * Composes the user list query into an explicit, narrowed view model
 * (loading / empty / error / success) for the presentational view.
 */
export function useUsersPage(): UsersPageState {
  const query = useUserList()

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

  return { status: 'success', users: query.data.items }
}
