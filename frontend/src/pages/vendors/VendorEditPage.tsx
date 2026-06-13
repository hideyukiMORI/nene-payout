import { Navigate, useParams } from 'react-router-dom'
import { EditVendorForm } from '@/features/manage-vendors'

export function VendorEditPage() {
  const { vendorId } = useParams<{ vendorId: string }>()

  if (vendorId === undefined) {
    return <Navigate to="/vendors" replace />
  }

  return <EditVendorForm vendorId={vendorId} />
}
