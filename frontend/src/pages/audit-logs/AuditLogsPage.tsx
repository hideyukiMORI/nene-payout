import { AuditLogListView, useAuditLogsPage } from '@/features/view-audit-logs'

export function AuditLogsPage() {
  const state = useAuditLogsPage()

  return <AuditLogListView state={state} />
}
