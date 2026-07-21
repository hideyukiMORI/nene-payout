import { afterAll, afterEach, beforeAll, describe, expect, it } from 'vitest'
import { waitFor } from '@testing-library/react'
import { mswServer } from '@tests/msw/server'
import { emptyOrganizationsManagementHandlers } from '@tests/msw/handlers/organization'
import { renderHookWithProviders } from '@tests/render/render-with-providers'
import { useOrganizationsPage } from './use-organizations-page'

describe('useOrganizationsPage', () => {
  beforeAll(() => {
    mswServer.listen()
  })
  afterEach(() => {
    mswServer.resetHandlers()
  })
  afterAll(() => {
    mswServer.close()
  })

  it('loads organizations and reaches the success state', async () => {
    const { result } = renderHookWithProviders(() => useOrganizationsPage())

    expect(result.current.status).toBe('loading')

    await waitFor(() => {
      expect(result.current.status).toBe('success')
    })

    if (result.current.status !== 'success') {
      throw new Error('expected success state')
    }
    expect(result.current.organizations).toHaveLength(2)
  })

  it('reaches the empty state when there are no organizations', async () => {
    mswServer.use(...emptyOrganizationsManagementHandlers)
    const { result } = renderHookWithProviders(() => useOrganizationsPage())

    await waitFor(() => {
      expect(result.current.status).toBe('empty')
    })
  })
})
