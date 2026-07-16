import { afterAll, afterEach, beforeAll, describe, expect, it } from 'vitest'
import { waitFor } from '@testing-library/react'
import { mswServer } from '../../../../tests/msw/server'
import { errorVendorHandlers } from '../../../../tests/msw/handlers/vendor'
import { renderHookWithProviders } from '../../../../tests/render/render-with-providers'
import { useDashboard } from './use-dashboard'

describe('useDashboard', () => {
  beforeAll(() => {
    mswServer.listen()
  })
  afterEach(() => {
    mswServer.resetHandlers()
  })
  afterAll(() => {
    mswServer.close()
  })

  it('aggregates resource totals into summary cards', async () => {
    const { result } = renderHookWithProviders(() => useDashboard())

    expect(result.current.status).toBe('loading')

    await waitFor(() => {
      expect(result.current.status).toBe('success')
    })

    if (result.current.status !== 'success') {
      throw new Error('expected success state')
    }
    expect(result.current.cards.map((card) => card.key)).toEqual([
      'pendingInvoices',
      'totalInvoices',
      'vendors',
      'payments',
    ])
    expect(result.current.cards.every((card) => typeof card.count === 'number')).toBe(true)
  })

  it('reaches the error state when a query fails', async () => {
    mswServer.use(...errorVendorHandlers)
    const { result } = renderHookWithProviders(() => useDashboard())

    await waitFor(() => {
      expect(result.current.status).toBe('error')
    })
  })
})
