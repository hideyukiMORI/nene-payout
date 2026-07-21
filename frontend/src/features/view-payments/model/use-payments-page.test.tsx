import { afterAll, afterEach, beforeAll, describe, expect, it } from 'vitest'
import { waitFor } from '@testing-library/react'
import { mswServer } from '@tests/msw/server'
import {
  emptyPaymentExecutionHandlers,
  errorPaymentExecutionHandlers,
} from '@tests/msw/handlers/payment-execution'
import { renderHookWithProviders } from '@tests/render/render-with-providers'
import { usePaymentsPage } from './use-payments-page'

describe('usePaymentsPage', () => {
  beforeAll(() => {
    mswServer.listen()
  })
  afterEach(() => {
    mswServer.resetHandlers()
  })
  afterAll(() => {
    mswServer.close()
  })

  it('loads payment executions and reaches the success state', async () => {
    const { result } = renderHookWithProviders(() => usePaymentsPage())

    expect(result.current.status).toBe('loading')

    await waitFor(() => {
      expect(result.current.status).toBe('success')
    })

    if (result.current.status !== 'success') {
      throw new Error('expected success state')
    }
    expect(result.current.payments).toHaveLength(2)
  })

  it('reaches the empty state when there are no payments', async () => {
    mswServer.use(...emptyPaymentExecutionHandlers)
    const { result } = renderHookWithProviders(() => usePaymentsPage())

    await waitFor(() => {
      expect(result.current.status).toBe('empty')
    })
  })

  it('reaches the error state and exposes retry on server error', async () => {
    mswServer.use(...errorPaymentExecutionHandlers)
    const { result } = renderHookWithProviders(() => usePaymentsPage())

    await waitFor(() => {
      expect(result.current.status).toBe('error')
    })

    if (result.current.status !== 'error') {
      throw new Error('expected error state')
    }
    expect(typeof result.current.retry).toBe('function')
  })
})
