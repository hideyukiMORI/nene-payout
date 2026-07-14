import { useNavigate } from 'react-router-dom'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { ASSIGNABLE_ROLES, useInviteUser } from '@/entities/user'
import { Button, FormField, Input, PageHeader, Select, Text } from '@/shared/ui'
import { useTranslation, type MessageKey } from '@/shared/i18n'
import { ROLE_LABEL_KEY } from '../model/labels'
import {
  EMPTY_INVITE_USER_FORM_VALUES,
  inviteFormValuesToInput,
  inviteUserFormSchema,
  type InviteUserFormValues,
} from '../model/user-forms'

const USERS_PATH = '/users'

export function InviteUserForm() {
  const { t } = useTranslation()
  const navigate = useNavigate()
  const mutation = useInviteUser()
  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<InviteUserFormValues>({
    resolver: zodResolver(inviteUserFormSchema),
    defaultValues: EMPTY_INVITE_USER_FORM_VALUES,
  })

  const errorText = (message: string | undefined): string | null =>
    message !== undefined ? t(message as MessageKey) : null

  return (
    <section className="px-x-inline-md">
      <PageHeader title={t('admin.users.newTitle')} />
      <form
        noValidate
        onSubmit={(event) => {
          void handleSubmit((values) => {
            mutation.mutate(inviteFormValuesToInput(values), {
              onSuccess: () => {
                void navigate(USERS_PATH)
              },
            })
          })(event)
        }}
        className="flex flex-col gap-x-stack-md"
      >
        <FormField
          id="user-email"
          label={t('admin.users.field.email')}
          error={errorText(errors.email?.message)}
        >
          <Input
            id="user-email"
            type="email"
            inputMode="email"
            aria-invalid={errors.email !== undefined}
            {...register('email')}
          />
        </FormField>

        <FormField
          id="user-role"
          label={t('admin.users.field.role')}
          error={errorText(errors.role?.message)}
        >
          <Select id="user-role" aria-invalid={errors.role !== undefined} {...register('role')}>
            {ASSIGNABLE_ROLES.map((role) => (
              <option key={role} value={role}>
                {t(ROLE_LABEL_KEY[role])}
              </option>
            ))}
          </Select>
        </FormField>

        {mutation.isError ? (
          <Text tone="muted">
            <span role="alert" className="text-danger">
              {t('admin.users.form.inviteFailed')}
            </span>
          </Text>
        ) : null}

        <div className="flex gap-x-inline-sm">
          <Button type="submit" disabled={mutation.isPending}>
            {mutation.isPending ? t('common.actions.saving') : t('admin.users.actions.invite')}
          </Button>
          <Button
            type="button"
            variant="secondary"
            disabled={mutation.isPending}
            onClick={() => {
              void navigate(USERS_PATH)
            }}
          >
            {t('common.actions.cancel')}
          </Button>
        </div>
      </form>
    </section>
  )
}
