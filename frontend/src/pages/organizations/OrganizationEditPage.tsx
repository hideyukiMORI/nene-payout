import { Navigate, useParams } from 'react-router-dom'
import { EditOrganizationForm } from '@/features/manage-organizations'

export function OrganizationEditPage() {
  const { organizationId } = useParams<{ organizationId: string }>()

  if (organizationId === undefined) {
    return <Navigate to="/organizations" replace />
  }

  return <EditOrganizationForm organizationId={organizationId} />
}
