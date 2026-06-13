import { PageHeader, Text } from '@/shared/ui'
import { useTranslation } from '@/shared/i18n'

export function LoginPage() {
  const { t } = useTranslation()

  return (
    <section className="px-inline-md">
      <PageHeader title={t('auth.login.title')} />
      <Text tone="muted">{t('auth.login.submit')}</Text>
    </section>
  )
}
