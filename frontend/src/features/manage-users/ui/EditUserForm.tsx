import { useNavigate } from 'react-router-dom'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { ASSIGNABLE_ROLES, toUserId, useUpdateUserRole, useUser } from '@/entities/user'
import { Button, ErrorState, FormField, PageHeader, Select, Spinner, Text } from '@/shared/ui'
import { useTranslation, type MessageKey } from '@/shared/i18n'
import { ROLE_LABEL_KEY } from '../model/labels'
import {
  editFormValuesToInput,
  editUserRoleFormSchema,
  userToRoleFormValues,
  type EditUserRoleFormValues,
} from '../model/user-forms'

const USERS_PATH = '/users'

export interface EditUserFormProps {
  userId: string
}

export function EditUserForm({ userId }: EditUserFormProps) {
  const { t } = useTranslation()
  const id = toUserId(userId)
  const query = useUser(id)

  return (
    <section className="px-inline-md">
      <PageHeader title={t('admin.users.editTitle')} />
      {query.isPending ? (
        <Spinner label={t('common.state.loading')} />
      ) : query.isError ? (
        <ErrorState
          message={t('common.state.error')}
          retryLabel={t('common.actions.retry')}
          onRetry={() => {
            void query.refetch()
          }}
        />
      ) : (
        <EditUserRoleFields userId={userId} defaultValues={userToRoleFormValues(query.data)} />
      )}
    </section>
  )
}

function EditUserRoleFields({
  userId,
  defaultValues,
}: {
  userId: string
  defaultValues: EditUserRoleFormValues
}) {
  const { t } = useTranslation()
  const navigate = useNavigate()
  const mutation = useUpdateUserRole()
  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<EditUserRoleFormValues>({
    resolver: zodResolver(editUserRoleFormSchema),
    defaultValues,
  })

  const errorText = (message: string | undefined): string | null =>
    message !== undefined ? t(message as MessageKey) : null

  return (
    <form
      noValidate
      onSubmit={(event) => {
        void handleSubmit((values) => {
          mutation.mutate(
            { id: toUserId(userId), input: editFormValuesToInput(values) },
            {
              onSuccess: () => {
                void navigate(USERS_PATH)
              },
            },
          )
        })(event)
      }}
      className="flex flex-col gap-stack-md"
    >
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
            {t('admin.users.form.saveFailed')}
          </span>
        </Text>
      ) : null}

      <div className="flex gap-inline-sm">
        <Button type="submit" disabled={mutation.isPending}>
          {mutation.isPending ? t('common.actions.saving') : t('common.actions.save')}
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
  )
}
