import { PageHeader, Text } from '@/shared/ui'
import { useTranslation } from '@/shared/i18n'

export function ForbiddenPage() {
  const { t } = useTranslation()

  return (
    <section className="px-inline-md">
      <PageHeader title={t('common.error.forbidden')} />
      <Text tone="muted">{t('common.error.forbidden')}</Text>
    </section>
  )
}
