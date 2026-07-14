import { Link } from 'react-router-dom'
import { ErrorState, PageHeader, Spinner, Text } from '@/shared/ui'
import { useTranslation } from '@/shared/i18n'
import type { DashboardState } from '../hooks/use-dashboard'

export interface DashboardViewProps {
  state: DashboardState
}

export function DashboardView({ state }: DashboardViewProps) {
  const { t } = useTranslation()

  return (
    <section className="px-inline-md">
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
        <ul className="grid grid-cols-2 gap-stack-md">
          {state.cards.map((card) => (
            <li key={card.key}>
              <Link
                to={card.to}
                className="flex flex-col gap-stack-sm rounded-md border border-border bg-surface-raised px-inline-md py-stack-md"
              >
                <Text tone="muted">{t(card.labelKey)}</Text>
                <span className="font-sans text-heading font-medium text-text-primary">
                  {card.count ?? '—'}
                </span>
                <Text tone="muted">{t('admin.dashboard.view')}</Text>
              </Link>
            </li>
          ))}
        </ul>
      )
  }
}
