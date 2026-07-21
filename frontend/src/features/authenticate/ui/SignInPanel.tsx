import { useNavigate } from 'react-router-dom'
import { useLogin } from '@/entities/session'
import { PageHeader } from '@/shared/ui/components/PageHeader'
import { useTranslation } from '@/shared/i18n'
import { LoginForm } from './LoginForm'

/** Where to land after a successful sign-in (the app's default authed view). */
const HOME_PATH = '/dashboard'

export function SignInPanel() {
  const { t } = useTranslation()
  const navigate = useNavigate()
  const mutation = useLogin()

  return (
    <section className="mx-auto flex max-w-md flex-col gap-x-stack-md px-x-inline-md py-x-stack-lg">
      <PageHeader title={t('auth.login.title')} />
      <LoginForm
        submitting={mutation.isPending}
        submitError={mutation.isError}
        onSubmit={(credentials) => {
          mutation.mutate(credentials, {
            onSuccess: () => {
              void navigate(HOME_PATH, { replace: true })
            },
          })
        }}
      />
    </section>
  )
}
