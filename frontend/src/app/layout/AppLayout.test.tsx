import { afterAll, afterEach, beforeAll, beforeEach, describe, expect, it } from 'vitest'
import { render, screen, fireEvent } from '@testing-library/react'
import { QueryClientProvider } from '@tanstack/react-query'
import { MemoryRouter, Route, Routes } from 'react-router-dom'
import { I18nProvider } from '@/shared/i18n'
import { authToken } from '@/shared/api/auth-token'
import { mswServer } from '@tests/msw/server'
import { currentUserHandlers } from '@tests/msw/handlers/session'
import { createTestQueryClient } from '@tests/render/render-with-providers'
import { AppLayout } from './AppLayout'

function renderShell(initialPath: string) {
  return render(
    <I18nProvider>
      <QueryClientProvider client={createTestQueryClient()}>
        <MemoryRouter initialEntries={[initialPath]}>
          <Routes>
            <Route element={<AppLayout />}>
              <Route path="/dashboard" element={<div>dashboard-page</div>} />
              <Route path="/vendors" element={<div>vendors-page</div>} />
            </Route>
            <Route path="/login" element={<div>login-page</div>} />
          </Routes>
        </MemoryRouter>
      </QueryClientProvider>
    </I18nProvider>,
  )
}

describe('AppLayout', () => {
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
    localStorage.setItem('nene-payout-locale', 'ja')
    authToken.set('test-token')
  })

  it('renders the full nav for an admin', async () => {
    renderShell('/vendors')

    const nav = screen.getByRole('navigation', { name: 'メイン' })
    expect(nav).toBeInTheDocument()
    expect(await screen.findByRole('link', { name: '仕入先' })).toBeInTheDocument()
    expect(screen.getByRole('link', { name: '受取請求書' })).toBeInTheDocument()
    expect(screen.getByRole('link', { name: '決済' })).toBeInTheDocument()
    expect(screen.getByRole('link', { name: '監査ログ' })).toBeInTheDocument()
    expect(screen.getByText('vendors-page')).toBeInTheDocument()
  })

  it('hides admin-only nav items for an operator', async () => {
    mswServer.use(...currentUserHandlers({ role: 'operator' }))
    renderShell('/dashboard')

    // received-invoices is capability-gated, so it appears only after /me
    // resolves — awaiting it guarantees the operator role has been applied.
    expect(await screen.findByRole('link', { name: '受取請求書' })).toBeInTheDocument()
    expect(screen.getByRole('link', { name: 'ダッシュボード' })).toBeInTheDocument()
    expect(screen.getByRole('link', { name: '決済' })).toBeInTheDocument()
    expect(screen.queryByRole('link', { name: '仕入先' })).not.toBeInTheDocument()
    expect(screen.queryByRole('link', { name: '監査ログ' })).not.toBeInTheDocument()
  })

  it('switches the locale from the shell selector', async () => {
    renderShell('/vendors')

    const selector = screen.getByRole('combobox', { name: '言語' })
    fireEvent.change(selector, { target: { value: 'en' } })

    expect(await screen.findByRole('link', { name: 'Vendors' })).toBeInTheDocument()
    expect(localStorage.getItem('nene-payout-locale')).toBe('en')
  })

  it('clears the token and navigates to login on sign out', () => {
    renderShell('/vendors')

    fireEvent.click(screen.getByRole('button', { name: 'ログアウト' }))

    expect(authToken.get()).toBeNull()
    expect(screen.getByText('login-page')).toBeInTheDocument()
  })
})
