import { useNavigate } from 'react-router-dom'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { useCreateOrganization } from '@/entities/organization'
import { Button, FormField, Input, PageHeader, Text } from '@/shared/ui'
import { useTranslation, type MessageKey } from '@/shared/i18n'
import {
  createFormValuesToInput,
  createOrganizationFormSchema,
  EMPTY_CREATE_ORGANIZATION_FORM_VALUES,
  type CreateOrganizationFormValues,
} from '../model/organization-management-forms'

const ORGANIZATIONS_PATH = '/organizations'

export function CreateOrganizationForm() {
  const { t } = useTranslation()
  const navigate = useNavigate()
  const mutation = useCreateOrganization()
  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<CreateOrganizationFormValues>({
    resolver: zodResolver(createOrganizationFormSchema),
    defaultValues: EMPTY_CREATE_ORGANIZATION_FORM_VALUES,
  })

  const errorText = (message: string | undefined): string | null =>
    message !== undefined ? t(message as MessageKey) : null

  return (
    <section className="px-inline-md">
      <PageHeader title={t('admin.organizations.newTitle')} />
      <form
        noValidate
        onSubmit={(event) => {
          void handleSubmit((values) => {
            mutation.mutate(createFormValuesToInput(values), {
              onSuccess: () => {
                void navigate(ORGANIZATIONS_PATH)
              },
            })
          })(event)
        }}
        className="flex flex-col gap-stack-md"
      >
        <FormField
          id="organization-slug"
          label={t('admin.organizations.field.slug')}
          error={errorText(errors.slug?.message)}
        >
          <Input
            id="organization-slug"
            aria-invalid={errors.slug !== undefined}
            {...register('slug')}
          />
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

        <div className="flex gap-inline-sm">
          <Button type="submit" disabled={mutation.isPending}>
            {mutation.isPending ? t('common.actions.saving') : t('common.actions.create')}
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
    </section>
  )
}
