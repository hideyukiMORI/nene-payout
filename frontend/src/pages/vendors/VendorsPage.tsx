import { useVendorsPage, VendorListView } from '@/features/manage-vendors'

export function VendorsPage() {
  const state = useVendorsPage()

  return <VendorListView state={state} />
}
