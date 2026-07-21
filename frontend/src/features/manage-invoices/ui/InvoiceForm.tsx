import { useFieldArray, useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import type { CreateReceivedInvoiceInput } from '@/entities/received-invoice'
import { Button } from '@/shared/ui/primitives/Button'
import { FormField } from '@/shared/ui/components/FormField'
import { Input } from '@/shared/ui/primitives/Input'
import { Select } from '@/shared/ui/primitives/Select'
import { Text } from '@/shared/ui/primitives/Text'
import { useTranslation } from '@/shared/i18n'
import {
  EMPTY_INVOICE_FORM_VALUES,
  formValuesToCreateInput,
  invoiceFormSchema,
  TAX_RATE_BPS_VALUES,
  type InvoiceFormValues,
} from '../model/invoice-form'

export interface InvoiceFormVendorOption {
  id: string
  name: string
}

export interface InvoiceFormProps {
  vendors: InvoiceFormVendorOption[]
  defaultValues?: InvoiceFormValues
  submitLabel: string
  submitting: boolean
  submitError: boolean
  onSubmit: (input: CreateReceivedInvoiceInput) => void
  onCancel: () => void
}

export function InvoiceForm({
  vendors,
  defaultValues = EMPTY_INVOICE_FORM_VALUES,
  submitLabel,
  submitting,
  submitError,
  onSubmit,
  onCancel,
}: InvoiceFormProps) {
  const { t } = useTranslation()
  const {
    register,
    control,
    handleSubmit,
    formState: { errors },
  } = useForm<InvoiceFormValues>({
    resolver: zodResolver(invoiceFormSchema),
    defaultValues,
  })
  const taxLines = useFieldArray({ control, name: 'taxBreakdown' })

  const errorText = (message: string | undefined): string | null =>
    message !== undefined ? t(message as Parameters<typeof t>[0]) : null

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
        id="invoice-vendor"
        label={t('admin.receivedInvoices.field.vendor')}
        error={errorText(errors.vendorId?.message)}
      >
        <Select
          id="invoice-vendor"
          aria-invalid={errors.vendorId !== undefined}
          {...register('vendorId')}
        >
          <option value="">—</option>
          {vendors.map((vendor) => (
            <option key={vendor.id} value={vendor.id}>
              {vendor.name}
            </option>
          ))}
        </Select>
      </FormField>

      <FormField
        id="invoice-amount"
        label={t('common.field.amount')}
        error={errorText(errors.amount?.message)}
      >
        <Input
          id="invoice-amount"
          type="number"
          inputMode="numeric"
          aria-invalid={errors.amount !== undefined}
          {...register('amount')}
        />
      </FormField>

      <FormField
        id="invoice-due-date"
        label={t('common.field.dueDate')}
        error={errorText(errors.dueDate?.message)}
      >
        <Input
          id="invoice-due-date"
          type="date"
          aria-invalid={errors.dueDate !== undefined}
          {...register('dueDate')}
        />
      </FormField>

      <FormField
        id="invoice-registration-number"
        label={t('admin.receivedInvoices.field.registrationNumber')}
        error={errorText(errors.registrationNumber?.message)}
      >
        <Input
          id="invoice-registration-number"
          aria-invalid={errors.registrationNumber !== undefined}
          {...register('registrationNumber')}
        />
      </FormField>

      <FormField id="invoice-vault-url" label={t('admin.receivedInvoices.field.vaultDocumentUrl')}>
        <Input id="invoice-vault-url" {...register('vaultDocumentUrl')} />
      </FormField>

      <fieldset className="flex flex-col gap-x-stack-sm border-t border-border py-x-stack-sm">
        <legend className="font-sans font-medium text-text-primary">
          {t('admin.receivedInvoices.taxBreakdown.title')}
        </legend>

        {taxLines.fields.map((field, index) => (
          <div key={field.id} className="flex items-end gap-x-inline-sm">
            <FormField
              id={`tax-rate-${index}`}
              label={t('admin.receivedInvoices.field.taxRate')}
              error={errorText(errors.taxBreakdown?.[index]?.taxRateBps?.message)}
            >
              <Select
                id={`tax-rate-${index}`}
                aria-invalid={errors.taxBreakdown?.[index]?.taxRateBps !== undefined}
                {...register(`taxBreakdown.${index}.taxRateBps`)}
              >
                <option value={TAX_RATE_BPS_VALUES[0]}>
                  {t('admin.receivedInvoices.taxBreakdown.rate10')}
                </option>
                <option value={TAX_RATE_BPS_VALUES[1]}>
                  {t('admin.receivedInvoices.taxBreakdown.rate8')}
                </option>
              </Select>
            </FormField>

            <FormField
              id={`tax-taxable-${index}`}
              label={t('admin.receivedInvoices.field.taxableAmount')}
              error={errorText(errors.taxBreakdown?.[index]?.taxableAmount?.message)}
            >
              <Input
                id={`tax-taxable-${index}`}
                type="number"
                inputMode="numeric"
                aria-invalid={errors.taxBreakdown?.[index]?.taxableAmount !== undefined}
                {...register(`taxBreakdown.${index}.taxableAmount`)}
              />
            </FormField>

            <FormField
              id={`tax-amount-${index}`}
              label={t('admin.receivedInvoices.field.taxAmount')}
              error={errorText(errors.taxBreakdown?.[index]?.taxAmount?.message)}
            >
              <Input
                id={`tax-amount-${index}`}
                type="number"
                inputMode="numeric"
                aria-invalid={errors.taxBreakdown?.[index]?.taxAmount !== undefined}
                {...register(`taxBreakdown.${index}.taxAmount`)}
              />
            </FormField>

            <Button
              type="button"
              variant="secondary"
              onClick={() => {
                taxLines.remove(index)
              }}
            >
              {t('admin.receivedInvoices.taxBreakdown.remove')}
            </Button>
          </div>
        ))}

        <div>
          <Button
            type="button"
            variant="secondary"
            onClick={() => {
              taxLines.append({
                taxRateBps: TAX_RATE_BPS_VALUES[0],
                taxableAmount: '',
                taxAmount: '',
              })
            }}
          >
            {t('admin.receivedInvoices.taxBreakdown.add')}
          </Button>
        </div>
      </fieldset>

      {submitError ? (
        <Text tone="muted">
          <span role="alert" className="text-danger">
            {t('admin.receivedInvoices.form.saveFailed')}
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
