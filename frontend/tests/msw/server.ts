import { setupServer } from 'msw/node'
import { vendorHandlers } from './handlers/vendor'
import { receivedInvoiceHandlers } from './handlers/received-invoice'
import { paymentExecutionHandlers } from './handlers/payment-execution'

export const mswServer = setupServer(
  ...vendorHandlers,
  ...receivedInvoiceHandlers,
  ...paymentExecutionHandlers,
)
