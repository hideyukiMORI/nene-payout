import { afterAll, afterEach, beforeAll, beforeEach, describe, expect, it } from 'vitest'
import { render, screen } from '@testing-library/react'
import { QueryClientProvider } from '@tanstack/react-query'
import { MemoryRouter, Navigate, Outlet, Route, Routes } from 'react-router-dom'
import { authToken } from '@/shared/api/auth-token'
import { mswServer } from '@tests/msw/server'
import { currentUserHandlers } from '@tests/msw/handlers/session'
import { createTestQueryClient } from '@tests/render/render-with-providers'
import { RequireCapability } from './require-capability'

function renderGuarded() {
  return render(
    <QueryClientProvider client={createTestQueryClient()}>
      <MemoryRouter initialEntries={['/vendors']}>
        <Routes>
          <Route path="/forbidden" element={<div>forbidden-page</div>} />
          <Route
            element={
              <RequireCapability capability="ManageVendors">
                <Outlet />
              </RequireCapability>
            }
          >
            <Route path="/vendors" element={<div>vendors-page</div>} />
          </Route>
          <Route path="*" element={<Navigate to="/forbidden" replace />} />
        </Routes>
      </MemoryRouter>
    </QueryClientProvider>,
  )
}

describe('RequireCapability', () => {
  beforeAll(() => {
    mswServer.listen()
  })
  afterEach(() => {
    mswServer.resetHandlers()
    authToken.clear()
  })
  afterAll(() => {
    mswServer.close()
  })
  beforeEach(() => {
    authToken.set('test-token')
  })

  it('renders the protected route when the role has the capability', async () => {
    mswServer.use(...currentUserHandlers({ role: 'admin' }))
    renderGuarded()

    expect(await screen.findByText('vendors-page')).toBeInTheDocument()
  })

  it('redirects to /forbidden when the role lacks the capability', async () => {
    mswServer.use(...currentUserHandlers({ role: 'operator' }))
    renderGuarded()

    expect(await screen.findByText('forbidden-page')).toBeInTheDocument()
    expect(screen.queryByText('vendors-page')).not.toBeInTheDocument()
  })
})
