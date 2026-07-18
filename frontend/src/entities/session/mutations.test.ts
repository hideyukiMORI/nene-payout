import { afterAll, afterEach, beforeAll, beforeEach, describe, expect, it } from 'vitest'
import { waitFor } from '@testing-library/react'
import { mswServer } from '../../../tests/msw/server'
import {
  invalidCredentialsHandlers,
  sessionHandlers,
  TEST_TOKEN,
} from '../../../tests/msw/handlers/session'
import { renderHookWithProviders } from '../../../tests/render/render-with-providers'
import { authToken } from '@/shared/api/auth-token'
import { AppError } from '@/shared/api/client'
import { useLogin } from './mutations'

/**
 * useLogin is the single place a session is established: it exchanges
 * credentials for a bearer token and stores it, which is what unlocks the
 * fail-closed AuthGate. Pin that it only stores a token on success.
 */
describe('useLogin', () => {
  beforeAll(() => {
    mswServer.listen()
  })
  beforeEach(() => {
    authToken.clear()
  })
  afterEach(() => {
    authToken.clear()
    mswServer.resetHandlers()
  })
  afterAll(() => {
    mswServer.close()
  })

  it('exchanges credentials for a bearer token and stores it', async () => {
    mswServer.use(...sessionHandlers)
    const { result } = renderHookWithProviders(() => useLogin())

    result.current.mutate({ email: 'admin@example.com', password: 'correct-horse' })

    await waitFor(() => {
      expect(result.current.isSuccess).toBe(true)
    })
    expect(authToken.get()).toBe(TEST_TOKEN)
  })

  it('does not establish a session when credentials are rejected (401)', async () => {
    mswServer.use(...invalidCredentialsHandlers)
    const { result } = renderHookWithProviders(() => useLogin())

    result.current.mutate({ email: 'admin@example.com', password: 'wrong' })

    await waitFor(() => {
      expect(result.current.isError).toBe(true)
    })
    expect(result.current.error).toBeInstanceOf(AppError)
    expect(authToken.get()).toBeNull()
  })
})
