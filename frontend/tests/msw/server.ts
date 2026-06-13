import { setupServer } from 'msw/node'
import { vendorHandlers } from './handlers/vendor'
import { receivedInvoiceHandlers } from './handlers/received-invoice'

export const mswServer = setupServer(...vendorHandlers, ...receivedInvoiceHandlers)
