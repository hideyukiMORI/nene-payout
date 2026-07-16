import { useAuditLogList, type AuditLog } from '@/entities/audit-log'

export type AuditLogsPageState =
  | { status: 'loading' }
  | { status: 'error'; retry: () => void }
  | { status: 'empty' }
  | { status: 'success'; logs: AuditLog[] }

/**
 * Composes the audit-log list query into an explicit, narrowed view model
 * (loading / empty / error / success) for the presentational view.
 */
export function useAuditLogsPage(): AuditLogsPageState {
  const query = useAuditLogList()

  if (query.isPending) {
    return { status: 'loading' }
  }

  if (query.isError) {
    return {
      status: 'error',
      retry: () => {
        void query.refetch()
      },
    }
  }

  if (query.data.items.length === 0) {
    return { status: 'empty' }
  }

  return { status: 'success', logs: query.data.items }
}
