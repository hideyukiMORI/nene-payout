import { PageHeader } from '@/shared/ui/components/PageHeader'
import { Text } from '@/shared/ui/primitives/Text'
import { useTranslation } from '@/shared/i18n'

export function ForbiddenPage() {
  const { t } = useTranslation()

  return (
    <section className="px-x-inline-md">
      <PageHeader title={t('common.error.forbidden')} />
      <Text tone="muted">{t('common.error.forbidden')}</Text>
    </section>
  )
}
