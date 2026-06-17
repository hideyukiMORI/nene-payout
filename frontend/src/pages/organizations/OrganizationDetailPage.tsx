import { Navigate, useParams } from 'react-router-dom'
import { OrganizationDetailView } from '@/features/manage-organizations'

export function OrganizationDetailPage() {
  const { organizationId } = useParams<{ organizationId: string }>()

  if (organizationId === undefined) {
    return <Navigate to="/organizations" replace />
  }

  return <OrganizationDetailView organizationId={organizationId} />
}
