import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import {
  useOrganizationSettings,
  useUpdateOrganizationName,
  type Organization,
} from '@/entities/organization'
import {
  Button,
  DetailList,
  ErrorState,
  FormField,
  Input,
  PageHeader,
  Spinner,
  Text,
} from '@/shared/ui'
import { useTranslation, type MessageKey } from '@/shared/i18n'
import {
  formValuesToUpdateInput,
  organizationFormSchema,
  organizationToFormValues,
  type OrganizationFormValues,
} from '../model/organization-form'

const EMPTY = '—'

export function OrganizationSettingsForm() {
  const { t } = useTranslation()
  const query = useOrganizationSettings()

  return (
    <section className="px-x-inline-md">
      <PageHeader title={t('admin.organization.pageTitle')} />
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
        <OrganizationSettingsFields organization={query.data} />
      )}
    </section>
  )
}

function OrganizationSettingsFields({ organization }: { organization: Organization }) {
  const { t } = useTranslation()
  const mutation = useUpdateOrganizationName()
  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<OrganizationFormValues>({
    resolver: zodResolver(organizationFormSchema),
    defaultValues: organizationToFormValues(organization),
  })

  const errorText = (message: string | undefined): string | null =>
    message !== undefined ? t(message as MessageKey) : null

  return (
    <div className="flex flex-col gap-x-stack-md">
      <DetailList
        rows={[
          { label: t('admin.organization.field.slug'), value: organization.slug },
          {
            label: t('admin.organization.field.customDomain'),
            value: organization.customDomain ?? EMPTY,
          },
        ]}
      />
      <form
        noValidate
        onSubmit={(event) => {
          void handleSubmit((values) => {
            mutation.mutate(formValuesToUpdateInput(values))
          })(event)
        }}
        className="flex flex-col gap-x-stack-md"
      >
        <FormField
          id="organization-name"
          label={t('admin.organization.field.name')}
          error={errorText(errors.name?.message)}
        >
          <Input
            id="organization-name"
            aria-invalid={errors.name !== undefined}
            {...register('name')}
          />
        </FormField>

        {mutation.isError ? (
          <Text tone="muted">
            <span role="alert" className="text-danger">
              {t('admin.organization.form.saveFailed')}
            </span>
          </Text>
        ) : null}

        {mutation.isSuccess ? (
          <Text tone="muted">
            <span role="status">{t('admin.organization.form.saved')}</span>
          </Text>
        ) : null}

        <div>
          <Button type="submit" disabled={mutation.isPending}>
            {mutation.isPending ? t('common.actions.saving') : t('common.actions.save')}
          </Button>
        </div>
      </form>
    </div>
  )
}
