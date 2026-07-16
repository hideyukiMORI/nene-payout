import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { ACCOUNT_TYPES, type CreateVendorInput } from '@/entities/vendor'
import { Button, FormField, Input, Select, Text } from '@/shared/ui'
import { useTranslation, type MessageKey } from '@/shared/i18n'
import {
  EMPTY_VENDOR_FORM_VALUES,
  formValuesToCreateInput,
  vendorFormSchema,
  type VendorFormValues,
} from '../model/vendor-form'

const ACCOUNT_TYPE_LABEL_KEY: Record<(typeof ACCOUNT_TYPES)[number], MessageKey> = {
  普通: 'admin.vendors.accountType.ordinary',
  当座: 'admin.vendors.accountType.checking',
}

export interface VendorFormProps {
  defaultValues?: VendorFormValues
  submitLabel: string
  submitting: boolean
  submitError: boolean
  onSubmit: (input: CreateVendorInput) => void
  onCancel: () => void
}

export function VendorForm({
  defaultValues = EMPTY_VENDOR_FORM_VALUES,
  submitLabel,
  submitting,
  submitError,
  onSubmit,
  onCancel,
}: VendorFormProps) {
  const { t } = useTranslation()
  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<VendorFormValues>({
    resolver: zodResolver(vendorFormSchema),
    defaultValues,
  })

  const errorText = (message: string | undefined): string | null =>
    message !== undefined ? t(message as MessageKey) : null

  return (
    <form
      noValidate
      onSubmit={(event) => {
        void handleSubmit((values) => {
          onSubmit(formValuesToCreateInput(values))
        })(event)
      }}
      className="flex flex-col gap-x-stack-md"
    >
      <FormField
        id="vendor-name"
        label={t('admin.vendors.field.name')}
        error={errorText(errors.name?.message)}
      >
        <Input id="vendor-name" aria-invalid={errors.name !== undefined} {...register('name')} />
      </FormField>

      <FormField
        id="vendor-bank-code"
        label={t('admin.vendors.field.bankCode')}
        error={errorText(errors.bankCode?.message)}
      >
        <Input
          id="vendor-bank-code"
          inputMode="numeric"
          aria-invalid={errors.bankCode !== undefined}
          {...register('bankCode')}
        />
      </FormField>

      <FormField
        id="vendor-branch-code"
        label={t('admin.vendors.field.branchCode')}
        error={errorText(errors.branchCode?.message)}
      >
        <Input
          id="vendor-branch-code"
          inputMode="numeric"
          aria-invalid={errors.branchCode !== undefined}
          {...register('branchCode')}
        />
      </FormField>

      <FormField
        id="vendor-account-type"
        label={t('admin.vendors.field.accountType')}
        error={errorText(errors.accountType?.message)}
      >
        <Select
          id="vendor-account-type"
          aria-invalid={errors.accountType !== undefined}
          {...register('accountType')}
        >
          {ACCOUNT_TYPES.map((type) => (
            <option key={type} value={type}>
              {t(ACCOUNT_TYPE_LABEL_KEY[type])}
            </option>
          ))}
        </Select>
      </FormField>

      <FormField
        id="vendor-account-number"
        label={t('admin.vendors.field.accountNumber')}
        error={errorText(errors.accountNumber?.message)}
      >
        <Input
          id="vendor-account-number"
          inputMode="numeric"
          aria-invalid={errors.accountNumber !== undefined}
          {...register('accountNumber')}
        />
      </FormField>

      <FormField
        id="vendor-account-name"
        label={t('admin.vendors.field.accountName')}
        error={errorText(errors.accountName?.message)}
      >
        <Input
          id="vendor-account-name"
          aria-invalid={errors.accountName !== undefined}
          {...register('accountName')}
        />
      </FormField>

      <FormField
        id="vendor-registration-number"
        label={t('admin.vendors.field.registrationNumber')}
        error={errorText(errors.registrationNumber?.message)}
      >
        <Input
          id="vendor-registration-number"
          aria-invalid={errors.registrationNumber !== undefined}
          {...register('registrationNumber')}
        />
      </FormField>

      {submitError ? (
        <Text tone="muted">
          <span role="alert" className="text-danger">
            {t('admin.vendors.form.saveFailed')}
          </span>
        </Text>
      ) : null}

      <div className="flex gap-x-inline-sm">
        <Button type="submit" disabled={submitting}>
          {submitting ? t('common.actions.saving') : submitLabel}
        </Button>
        <Button type="button" variant="secondary" onClick={onCancel} disabled={submitting}>
          {t('common.actions.cancel')}
        </Button>
      </div>
    </form>
  )
}
