import { afterAll, afterEach, beforeAll, describe, expect, it } from 'vitest'
import { waitFor } from '@testing-library/react'
import { mswServer } from '../../../../tests/msw/server'
import { emptyUserHandlers, errorUserHandlers } from '../../../../tests/msw/handlers/user'
import { renderHookWithProviders } from '../../../../tests/render/render-with-providers'
import { useUsersPage } from './use-users-page'

describe('useUsersPage', () => {
  beforeAll(() => {
    mswServer.listen()
  })
  afterEach(() => {
    mswServer.resetHandlers()
  })
  afterAll(() => {
    mswServer.close()
  })

  it('loads users and reaches the success state', async () => {
    const { result } = renderHookWithProviders(() => useUsersPage())

    expect(result.current.status).toBe('loading')

    await waitFor(() => {
      expect(result.current.status).toBe('success')
    })

    if (result.current.status !== 'success') {
      throw new Error('expected success state')
    }
    expect(result.current.users).toHaveLength(2)
  })

  it('reaches the empty state when there are no users', async () => {
    mswServer.use(...emptyUserHandlers)
    const { result } = renderHookWithProviders(() => useUsersPage())

    await waitFor(() => {
      expect(result.current.status).toBe('empty')
    })
  })

  it('reaches the error state and exposes retry on server error', async () => {
    mswServer.use(...errorUserHandlers)
    const { result } = renderHookWithProviders(() => useUsersPage())

    await waitFor(() => {
      expect(result.current.status).toBe('error')
    })

    if (result.current.status !== 'error') {
      throw new Error('expected error state')
    }
    expect(typeof result.current.retry).toBe('function')
  })
})
