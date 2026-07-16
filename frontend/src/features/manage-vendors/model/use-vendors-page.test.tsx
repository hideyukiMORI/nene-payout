import { afterAll, afterEach, beforeAll, describe, expect, it } from 'vitest'
import { waitFor } from '@testing-library/react'
import { mswServer } from '../../../../tests/msw/server'
import { emptyVendorHandlers, errorVendorHandlers } from '../../../../tests/msw/handlers/vendor'
import { renderHookWithProviders } from '../../../../tests/render/render-with-providers'
import { useVendorsPage } from './use-vendors-page'

describe('useVendorsPage', () => {
  beforeAll(() => {
    mswServer.listen()
  })
  afterEach(() => {
    mswServer.resetHandlers()
  })
  afterAll(() => {
    mswServer.close()
  })

  it('loads vendors and reaches the success state', async () => {
    const { result } = renderHookWithProviders(() => useVendorsPage())

    expect(result.current.status).toBe('loading')

    await waitFor(() => {
      expect(result.current.status).toBe('success')
    })

    if (result.current.status !== 'success') {
      throw new Error('expected success state')
    }
    expect(result.current.vendors).toHaveLength(2)
  })

  it('reaches the empty state when there are no vendors', async () => {
    mswServer.use(...emptyVendorHandlers)
    const { result } = renderHookWithProviders(() => useVendorsPage())

    await waitFor(() => {
      expect(result.current.status).toBe('empty')
    })
  })

  it('reaches the error state and exposes retry on server error', async () => {
    mswServer.use(...errorVendorHandlers)
    const { result } = renderHookWithProviders(() => useVendorsPage())

    await waitFor(() => {
      expect(result.current.status).toBe('error')
    })

    if (result.current.status !== 'error') {
      throw new Error('expected error state')
    }
    expect(typeof result.current.retry).toBe('function')
  })
})
