import { Link } from 'react-router-dom'
import { ErrorState } from '@/shared/ui/components/ErrorState'
import { PageHeader } from '@/shared/ui/components/PageHeader'
import { Spinner } from '@/shared/ui/primitives/Spinner'
import { Text } from '@/shared/ui/primitives/Text'
import { useTranslation } from '@/shared/i18n'
import type { DashboardState } from '../model/use-dashboard'

export interface DashboardViewProps {
  state: DashboardState
}

export function DashboardView({ state }: DashboardViewProps) {
  const { t } = useTranslation()

  return (
    <section className="px-x-inline-md">
      <PageHeader title={t('admin.dashboard.pageTitle')} />
      <DashboardBody state={state} />
    </section>
  )
}

function DashboardBody({ state }: DashboardViewProps) {
  const { t } = useTranslation()

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
    case 'success':
      return (
        <ul className="grid grid-cols-2 gap-x-stack-md">
          {state.cards.map((card) => (
            <li key={card.key}>
              <Link
                to={card.to}
                className="flex flex-col gap-x-stack-sm rounded-x-md border border-border bg-surface-raised px-x-inline-md py-x-stack-md"
              >
                <Text tone="muted">{t(card.labelKey)}</Text>
                <span className="font-sans font-medium text-text-primary">{card.count ?? '—'}</span>
                <Text tone="muted">{t('admin.dashboard.view')}</Text>
              </Link>
            </li>
          ))}
        </ul>
      )
  }
}
