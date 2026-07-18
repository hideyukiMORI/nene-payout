import { afterAll, afterEach, beforeAll, beforeEach, describe, expect, it } from 'vitest'
import { waitFor } from '@testing-library/react'
import { mswServer } from '../../../tests/msw/server'
import { currentUserHandlers } from '../../../tests/msw/handlers/session'
import { renderHookWithProviders } from '../../../tests/render/render-with-providers'
import { authToken } from '@/shared/api/auth-token'
import { useCurrentUser } from './queries'

/**
 * useCurrentUser mirrors the authenticated user's role for nav/route gating.
 * It must stay disabled without a token (no /auth/me call before sign-in) and
 * map the wire DTO through the domain mapper — including the fail-closed
 * null-role for an unrecognized wire role.
 */
describe('useCurrentUser', () => {
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

  it('is disabled and does not fetch without a token', () => {
    const { result } = renderHookWithProviders(() => useCurrentUser())

    expect(result.current.fetchStatus).toBe('idle')
    expect(result.current.data).toBeUndefined()
  })

  it('loads and maps the current user when a token is present', async () => {
    authToken.set('test.jwt.token')
    mswServer.use(
      ...currentUserHandlers({
        id: 'user-1',
        email: 'admin@example.com',
        role: 'admin',
        organization_id: 'org-1',
      }),
    )
    const { result } = renderHookWithProviders(() => useCurrentUser())

    await waitFor(() => {
      expect(result.current.isSuccess).toBe(true)
    })
    expect(result.current.data).toEqual({
      id: 'user-1',
      email: 'admin@example.com',
      role: 'admin',
      organizationId: 'org-1',
    })
  })

  it('fails closed to a null role when the wire role is unrecognized', async () => {
    authToken.set('test.jwt.token')
    mswServer.use(...currentUserHandlers({ role: 'wizard' }))
    const { result } = renderHookWithProviders(() => useCurrentUser())

    await waitFor(() => {
      expect(result.current.isSuccess).toBe(true)
    })
    expect(result.current.data?.role).toBeNull()
  })
})
