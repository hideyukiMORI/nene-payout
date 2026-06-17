import { setupServer } from 'msw/node'
import { sessionMeHandlers } from './handlers/session'
import { vendorHandlers, vendorDetailHandlers } from './handlers/vendor'
import { receivedInvoiceHandlers, receivedInvoiceDetailHandlers } from './handlers/received-invoice'
import {
  paymentExecutionHandlers,
  paymentExecutionDetailHandlers,
} from './handlers/payment-execution'
import { auditLogHandlers } from './handlers/audit-log'
import { userHandlers, userDetailHandlers } from './handlers/user'

export const mswServer = setupServer(
  ...sessionMeHandlers,
  ...vendorHandlers,
  ...vendorDetailHandlers,
  ...receivedInvoiceHandlers,
  ...receivedInvoiceDetailHandlers,
  ...paymentExecutionHandlers,
  ...paymentExecutionDetailHandlers,
  ...auditLogHandlers,
  ...userHandlers,
  ...userDetailHandlers,
)
