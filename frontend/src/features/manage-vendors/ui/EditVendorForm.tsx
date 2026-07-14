import { useNavigate } from 'react-router-dom'
import { toVendorId, useUpdateVendor, useVendor } from '@/entities/vendor'
import { ErrorState, PageHeader, Spinner } from '@/shared/ui'
import { useTranslation } from '@/shared/i18n'
import { vendorToFormValues } from '../model/vendor-form'
import { VendorForm } from './VendorForm'

const VENDORS_PATH = '/vendors'

export interface EditVendorFormProps {
  vendorId: string
}

export function EditVendorForm({ vendorId }: EditVendorFormProps) {
  const { t } = useTranslation()
  const navigate = useNavigate()
  const id = toVendorId(vendorId)
  const query = useVendor(id)
  const mutation = useUpdateVendor()

  return (
    <section className="px-x-inline-md">
      <PageHeader title={t('admin.vendors.editTitle')} />
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
        <VendorForm
          defaultValues={vendorToFormValues(query.data)}
          submitLabel={t('common.actions.save')}
          submitting={mutation.isPending}
          submitError={mutation.isError}
          onCancel={() => {
            void navigate(VENDORS_PATH)
          }}
          onSubmit={(input) => {
            mutation.mutate(
              { id, input },
              {
                onSuccess: () => {
                  void navigate(VENDORS_PATH)
                },
              },
            )
          }}
        />
      )}
    </section>
  )
}
