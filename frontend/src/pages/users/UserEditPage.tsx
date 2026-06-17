import { Navigate, useParams } from 'react-router-dom'
import { EditUserForm } from '@/features/manage-users'

export function UserEditPage() {
  const { userId } = useParams<{ userId: string }>()

  if (userId === undefined) {
    return <Navigate to="/users" replace />
  }

  return <EditUserForm userId={userId} />
}
