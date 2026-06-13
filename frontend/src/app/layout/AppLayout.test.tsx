import { afterEach, beforeEach, describe, expect, it } from 'vitest'
import { render, screen, fireEvent } from '@testing-library/react'
import { MemoryRouter, Route, Routes } from 'react-router-dom'
import { I18nProvider } from '@/shared/i18n'
import { authToken } from '@/shared/api/auth-token'
import { AppLayout } from './AppLayout'

function renderShell(initialPath: string) {
  return render(
    <I18nProvider>
      <MemoryRouter initialEntries={[initialPath]}>
        <Routes>
          <Route element={<AppLayout />}>
            <Route path="/vendors" element={<div>vendors-page</div>} />
          </Route>
          <Route path="/login" element={<div>login-page</div>} />
        </Routes>
      </MemoryRouter>
    </I18nProvider>,
  )
}

describe('AppLayout', () => {
  beforeEach(() => {
    localStorage.setItem('nene-payout-locale', 'ja')
    authToken.set('test-token')
  })
  afterEach(() => {
    authToken.clear()
  })

  it('renders the primary nav and the routed page content', () => {
    renderShell('/vendors')

    const nav = screen.getByRole('navigation', { name: 'メイン' })
    expect(nav).toBeInTheDocument()
    expect(screen.getByRole('link', { name: '受取請求書' })).toBeInTheDocument()
    expect(screen.getByRole('link', { name: '仕入先' })).toBeInTheDocument()
    expect(screen.getByRole('link', { name: '決済' })).toBeInTheDocument()
    expect(screen.getByText('vendors-page')).toBeInTheDocument()
  })

  it('switches the locale from the shell selector', () => {
    renderShell('/vendors')

    const selector = screen.getByRole('combobox', { name: '言語' })
    fireEvent.change(selector, { target: { value: 'en' } })

    expect(screen.getByRole('link', { name: 'Vendors' })).toBeInTheDocument()
    expect(localStorage.getItem('nene-payout-locale')).toBe('en')
  })

  it('clears the token and navigates to login on sign out', () => {
    renderShell('/vendors')

    fireEvent.click(screen.getByRole('button', { name: 'ログアウト' }))

    expect(authToken.get()).toBeNull()
    expect(screen.getByText('login-page')).toBeInTheDocument()
  })
})
