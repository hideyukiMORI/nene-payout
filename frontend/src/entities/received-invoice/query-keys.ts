import type { ReceivedInvoiceId } from './ids'
import type { ReceivedInvoiceStatus } from './model'

export const receivedInvoiceKeys = {
  all: ['received-invoices'] as const,
  lists: () => [...receivedInvoiceKeys.all, 'list'] as const,
  list: (params: { limit: number; offset: number; status: ReceivedInvoiceStatus | null }) =>
    [...receivedInvoiceKeys.lists(), params] as const,
  details: () => [...receivedInvoiceKeys.all, 'detail'] as const,
  detail: (id: ReceivedInvoiceId) => [...receivedInvoiceKeys.details(), id] as const,
}
