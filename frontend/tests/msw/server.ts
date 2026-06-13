import { setupServer } from 'msw/node'
import { vendorHandlers } from './handlers/vendor'

export const mswServer = setupServer(...vendorHandlers)
