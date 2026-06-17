import { useUsersPage, UserListView } from '@/features/manage-users'

export function UsersPage() {
  const state = useUsersPage()

  return <UserListView state={state} />
}
