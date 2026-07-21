import { useNavigate } from 'react-router-dom'
import { useCreateVendor } from '@/entities/vendor'
import { PageHeader } from '@/shared/ui/components/PageHeader'
import { useTranslation } from '@/shared/i18n'
import { VendorForm } from './VendorForm'

const VENDORS_PATH = '/vendors'

export function CreateVendorForm() {
  const { t } = useTranslation()
  const navigate = useNavigate()
  const mutation = useCreateVendor()

  return (
    <section className="px-x-inline-md">
      <PageHeader title={t('admin.vendors.newTitle')} />
      <VendorForm
        submitLabel={t('common.actions.create')}
        submitting={mutation.isPending}
        submitError={mutation.isError}
        onCancel={() => {
          void navigate(VENDORS_PATH)
        }}
        onSubmit={(input) => {
          mutation.mutate(input, {
            onSuccess: () => {
              void navigate(VENDORS_PATH)
            },
          })
        }}
      />
    </section>
  )
}
