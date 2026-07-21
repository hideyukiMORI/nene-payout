import { useState } from 'react'
import { Button } from '@/shared/ui/primitives/Button'
import { FormField } from '@/shared/ui/components/FormField'
import { Text } from '@/shared/ui/primitives/Text'
import { useTranslation } from '@/shared/i18n'
import { validatePdfFile } from '../model/pdf-file'

export interface UploadInvoicePdfFormProps {
  submitting: boolean
  submitError: boolean
  onSubmit: (file: File) => void
  onCancel: () => void
}

export function UploadInvoicePdfForm({
  submitting,
  submitError,
  onSubmit,
  onCancel,
}: UploadInvoicePdfFormProps) {
  const { t } = useTranslation()
  const [file, setFile] = useState<File | null>(null)
  const [validationError, setValidationError] = useState<string | null>(null)

  return (
    <form
      noValidate
      onSubmit={(event) => {
        event.preventDefault()
        const error = validatePdfFile(file)
        if (error !== null) {
          setValidationError(t(error))
          return
        }
        setValidationError(null)
        if (file !== null) {
          onSubmit(file)
        }
      }}
      className="flex flex-col gap-x-stack-md"
    >
      <FormField
        id="invoice-pdf"
        label={t('admin.receivedInvoices.pdf.selectFile')}
        error={validationError}
      >
        <input
          id="invoice-pdf"
          type="file"
          accept="application/pdf"
          aria-invalid={validationError !== null}
          onChange={(event) => {
            setFile(event.target.files?.[0] ?? null)
            setValidationError(null)
          }}
          className="font-sans text-text-primary"
        />
      </FormField>

      {submitError ? (
        <Text tone="muted">
          <span role="alert" className="text-danger">
            {t('admin.receivedInvoices.pdf.failed')}
          </span>
        </Text>
      ) : null}

      <div className="flex gap-x-inline-sm">
        <Button type="submit" disabled={submitting}>
          {submitting ? t('common.actions.saving') : t('common.actions.upload')}
        </Button>
        <Button type="button" variant="secondary" onClick={onCancel} disabled={submitting}>
          {t('common.actions.cancel')}
        </Button>
      </div>
    </form>
  )
}
