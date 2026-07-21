import { EmptyState } from '@/shared/ui/components/EmptyState'
import { ErrorState } from '@/shared/ui/components/ErrorState'
import { PageHeader } from '@/shared/ui/components/PageHeader'
import { Spinner } from '@/shared/ui/primitives/Spinner'
import { Text } from '@/shared/ui/primitives/Text'
import { formatDateTime } from '@/shared/lib'
import { useTranslation } from '@/shared/i18n'
import type { AuditLogsPageState } from '../model/use-audit-logs-page'

export interface AuditLogListViewProps {
  state: AuditLogsPageState
}

export function AuditLogListView({ state }: AuditLogListViewProps) {
  const { t } = useTranslation()

  return (
    <section className="px-x-inline-md">
      <PageHeader title={t('admin.auditLogs.pageTitle')} />
      <AuditLogListBody state={state} />
    </section>
  )
}

function AuditLogListBody({ state }: AuditLogListViewProps) {
  const { t, locale } = useTranslation()

  switch (state.status) {
    case 'loading':
      return <Spinner label={t('common.state.loading')} />
    case 'error':
      return (
        <ErrorState
          message={t('common.state.error')}
          retryLabel={t('common.actions.retry')}
          onRetry={state.retry}
        />
      )
    case 'empty':
      return <EmptyState message={t('admin.auditLogs.empty')} />
    case 'success':
      return (
        <ul>
          {state.logs.map((log) => (
            <li key={log.id} className="border-b border-border py-x-stack-sm">
              <Text>
                <span className="font-medium text-text-primary">{log.action}</span>
              </Text>
              <Text tone="muted">
                {t('admin.auditLogs.field.createdAt')}: {formatDateTime(log.createdAt, locale)} ·{' '}
                {t('admin.auditLogs.field.actor')}: {log.actorEmail ?? log.actorUserId ?? '—'} ·{' '}
                {t('admin.auditLogs.field.entity')}: {log.entityType} / {log.entityId}
              </Text>
            </li>
          ))}
        </ul>
      )
  }
}
