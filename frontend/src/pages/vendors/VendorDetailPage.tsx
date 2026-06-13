import { Navigate, useParams } from 'react-router-dom'
import { VendorDetailView } from '@/features/manage-vendors'

export function VendorDetailPage() {
  const { vendorId } = useParams<{ vendorId: string }>()

  if (vendorId === undefined) {
    return <Navigate to="/vendors" replace />
  }

  return <VendorDetailView vendorId={vendorId} />
}
