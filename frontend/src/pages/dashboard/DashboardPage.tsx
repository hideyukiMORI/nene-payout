import { DashboardView, useDashboard } from '@/features/view-dashboard'

export function DashboardPage() {
  const state = useDashboard()

  return <DashboardView state={state} />
}
