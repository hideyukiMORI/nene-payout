import type { PaymentExecutionId } from './ids'
import type { PaymentExecutionStatus } from './model'

export const paymentExecutionKeys = {
  all: ['payment-executions'] as const,
  lists: () => [...paymentExecutionKeys.all, 'list'] as const,
  list: (params: {
    limit: number
    offset: number
    status: PaymentExecutionStatus | null
    receivedInvoiceId: string | null
  }) => [...paymentExecutionKeys.lists(), params] as const,
  details: () => [...paymentExecutionKeys.all, 'detail'] as const,
  detail: (id: PaymentExecutionId) => [...paymentExecutionKeys.details(), id] as const,
}
