import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import type { Credentials } from '@/entities/session'
import { Button } from '@/shared/ui/primitives/Button'
import { FormField } from '@/shared/ui/components/FormField'
import { Input } from '@/shared/ui/primitives/Input'
import { Text } from '@/shared/ui/primitives/Text'
import { useTranslation, type MessageKey } from '@/shared/i18n'
import {
  EMPTY_LOGIN_FORM_VALUES,
  formValuesToCredentials,
  loginFormSchema,
  type LoginFormValues,
} from '../model/login-form'

export interface LoginFormProps {
  submitting: boolean
  submitError: boolean
  onSubmit: (credentials: Credentials) => void
}

export function LoginForm({ submitting, submitError, onSubmit }: LoginFormProps) {
  const { t } = useTranslation()
  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<LoginFormValues>({
    resolver: zodResolver(loginFormSchema),
    defaultValues: EMPTY_LOGIN_FORM_VALUES,
  })

  const errorText = (message: string | undefined): string | null =>
    message !== undefined ? t(message as MessageKey) : null

  return (
    <form
      noValidate
      onSubmit={(event) => {
        void handleSubmit((values) => {
          onSubmit(formValuesToCredentials(values))
        })(event)
      }}
      className="flex flex-col gap-x-stack-md"
    >
      <FormField
        id="login-email"
        label={t('auth.login.emailLabel')}
        error={errorText(errors.email?.message)}
      >
        <Input
          id="login-email"
          type="email"
          autoComplete="email"
          aria-invalid={errors.email !== undefined}
          {...register('email')}
        />
      </FormField>

      <FormField
        id="login-password"
        label={t('auth.login.passwordLabel')}
        error={errorText(errors.password?.message)}
      >
        <Input
          id="login-password"
          type="password"
          autoComplete="current-password"
          aria-invalid={errors.password !== undefined}
          {...register('password')}
        />
      </FormField>

      {submitError ? (
        <Text tone="muted">
          <span role="alert" className="text-danger">
            {t('auth.login.failed')}
          </span>
        </Text>
      ) : null}

      <Button type="submit" disabled={submitting}>
        {submitting ? t('common.actions.saving') : t('auth.login.submit')}
      </Button>
    </form>
  )
}
