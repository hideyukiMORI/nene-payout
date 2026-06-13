import { setupServer } from 'msw/node'
import { vendorHandlers, vendorDetailHandlers } from './handlers/vendor'
import { receivedInvoiceHandlers, receivedInvoiceDetailHandlers } from './handlers/received-invoice'
import {
  paymentExecutionHandlers,
  paymentExecutionDetailHandlers,
} from './handlers/payment-execution'

export const mswServer = setupServer(
  ...vendorHandlers,
  ...vendorDetailHandlers,
  ...receivedInvoiceHandlers,
  ...receivedInvoiceDetailHandlers,
  ...paymentExecutionHandlers,
  ...paymentExecutionDetailHandlers,
)
