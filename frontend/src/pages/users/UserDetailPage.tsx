import { Navigate, useParams } from 'react-router-dom'
import { UserDetailView } from '@/features/manage-users'

export function UserDetailPage() {
  const { userId } = useParams<{ userId: string }>()

  if (userId === undefined) {
    return <Navigate to="/users" replace />
  }

  return <UserDetailView userId={userId} />
}
