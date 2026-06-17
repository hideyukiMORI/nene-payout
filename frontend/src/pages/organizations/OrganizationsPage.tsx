import { useOrganizationsPage, OrganizationListView } from '@/features/manage-organizations'

export function OrganizationsPage() {
  const state = useOrganizationsPage()

  return <OrganizationListView state={state} />
}
