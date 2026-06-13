import { InvoiceListView, useInvoicesPage } from '@/features/manage-invoices'

export function InvoicesPage() {
  const state = useInvoicesPage()

  return <InvoiceListView state={state} />
}
