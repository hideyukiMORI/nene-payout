import { useNavigate } from 'react-router-dom'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import {
  useOrganizationById,
  useUpdateOrganization,
  type Organization,
} from '@/entities/organization'
import { Button, ErrorState, FormField, Input, PageHeader, Spinner, Text } from '@/shared/ui'
import { useTranslation, type MessageKey } from '@/shared/i18n'
import {
  editFormValuesToInput,
  editOrganizationFormSchema,
  organizationToEditFormValues,
  type EditOrganizationFormValues,
} from '../model/organization-management-forms'

const ORGANIZATIONS_PATH = '/organizations'

export interface EditOrganizationFormProps {
  organizationId: string
}

export function EditOrganizationForm({ organizationId }: EditOrganizationFormProps) {
  const { t } = useTranslation()
  const query = useOrganizationById(organizationId)

  return (
    <section className="px-x-inline-md">
      <PageHeader title={t('admin.organizations.editTitle')} />
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
        <EditOrganizationFields organization={query.data} />
      )}
    </section>
  )
}

function EditOrganizationFields({ organization }: { organization: Organization }) {
  const { t } = useTranslation()
  const navigate = useNavigate()
  const mutation = useUpdateOrganization()
  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<EditOrganizationFormValues>({
    resolver: zodResolver(editOrganizationFormSchema),
    defaultValues: organizationToEditFormValues(organization),
  })

  const errorText = (message: string | undefined): string | null =>
    message !== undefined ? t(message as MessageKey) : null

  return (
    <form
      noValidate
      onSubmit={(event) => {
        void handleSubmit((values) => {
          mutation.mutate(
            { id: organization.id, input: editFormValuesToInput(values) },
            {
              onSuccess: () => {
                void navigate(ORGANIZATIONS_PATH)
              },
            },
          )
        })(event)
      }}
      className="flex flex-col gap-x-stack-md"
    >
      <FormField id="organization-slug" label={t('admin.organizations.field.slug')}>
        <Input id="organization-slug" value={organization.slug} disabled readOnly />
      </FormField>

      <FormField
        id="organization-name"
        label={t('admin.organizations.field.name')}
        error={errorText(errors.name?.message)}
      >
        <Input
          id="organization-name"
          aria-invalid={errors.name !== undefined}
          {...register('name')}
        />
      </FormField>

      <FormField
        id="organization-custom-domain"
        label={t('admin.organizations.field.customDomain')}
        error={errorText(errors.customDomain?.message)}
      >
        <Input
          id="organization-custom-domain"
          aria-invalid={errors.customDomain !== undefined}
          {...register('customDomain')}
        />
      </FormField>

      {mutation.isError ? (
        <Text tone="muted">
          <span role="alert" className="text-danger">
            {mutation.error.status === 409
              ? t('admin.organizations.form.conflict')
              : t('admin.organizations.form.saveFailed')}
          </span>
        </Text>
      ) : null}

      <div className="flex gap-x-inline-sm">
        <Button type="submit" disabled={mutation.isPending}>
          {mutation.isPending ? t('common.actions.saving') : t('common.actions.save')}
        </Button>
        <Button
          type="button"
          variant="secondary"
          disabled={mutation.isPending}
          onClick={() => {
            void navigate(ORGANIZATIONS_PATH)
          }}
        >
          {t('common.actions.cancel')}
        </Button>
      </div>
    </form>
  )
}
