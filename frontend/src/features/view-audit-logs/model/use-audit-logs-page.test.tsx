import { afterAll, afterEach, beforeAll, describe, expect, it } from 'vitest'
import { waitFor } from '@testing-library/react'
import { mswServer } from '@tests/msw/server'
import { emptyAuditLogHandlers, errorAuditLogHandlers } from '@tests/msw/handlers/audit-log'
import { renderHookWithProviders } from '@tests/render/render-with-providers'
import { useAuditLogsPage } from './use-audit-logs-page'

describe('useAuditLogsPage', () => {
  beforeAll(() => {
    mswServer.listen()
  })
  afterEach(() => {
    mswServer.resetHandlers()
  })
  afterAll(() => {
    mswServer.close()
  })

  it('loads audit logs and reaches the success state', async () => {
    const { result } = renderHookWithProviders(() => useAuditLogsPage())

    expect(result.current.status).toBe('loading')

    await waitFor(() => {
      expect(result.current.status).toBe('success')
    })

    if (result.current.status !== 'success') {
      throw new Error('expected success state')
    }
    expect(result.current.logs).toHaveLength(2)
  })

  it('reaches the empty state when there are no entries', async () => {
    mswServer.use(...emptyAuditLogHandlers)
    const { result } = renderHookWithProviders(() => useAuditLogsPage())

    await waitFor(() => {
      expect(result.current.status).toBe('empty')
    })
  })

  it('reaches the error state on failure', async () => {
    mswServer.use(...errorAuditLogHandlers)
    const { result } = renderHookWithProviders(() => useAuditLogsPage())

    await waitFor(() => {
      expect(result.current.status).toBe('error')
    })
  })
})
