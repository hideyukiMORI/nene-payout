import { afterAll, afterEach, beforeAll, describe, expect, it } from 'vitest'
import { waitFor } from '@testing-library/react'
import { mswServer } from '@tests/msw/server'
import {
  emptyReceivedInvoiceHandlers,
  errorReceivedInvoiceHandlers,
} from '@tests/msw/handlers/received-invoice'
import { renderHookWithProviders } from '@tests/render/render-with-providers'
import { useInvoicesPage } from './use-invoices-page'

describe('useInvoicesPage', () => {
  beforeAll(() => {
    mswServer.listen()
  })
  afterEach(() => {
    mswServer.resetHandlers()
  })
  afterAll(() => {
    mswServer.close()
  })

  it('loads received invoices and reaches the success state', async () => {
    const { result } = renderHookWithProviders(() => useInvoicesPage())

    expect(result.current.status).toBe('loading')

    await waitFor(() => {
      expect(result.current.status).toBe('success')
    })

    if (result.current.status !== 'success') {
      throw new Error('expected success state')
    }
    expect(result.current.invoices).toHaveLength(2)
  })

  it('reaches the empty state when there are no invoices', async () => {
    mswServer.use(...emptyReceivedInvoiceHandlers)
    const { result } = renderHookWithProviders(() => useInvoicesPage())

    await waitFor(() => {
      expect(result.current.status).toBe('empty')
    })
  })

  it('reaches the error state and exposes retry on server error', async () => {
    mswServer.use(...errorReceivedInvoiceHandlers)
    const { result } = renderHookWithProviders(() => useInvoicesPage())

    await waitFor(() => {
      expect(result.current.status).toBe('error')
    })

    if (result.current.status !== 'error') {
      throw new Error('expected error state')
    }
    expect(typeof result.current.retry).toBe('function')
  })
})
