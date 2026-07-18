import { afterEach, beforeEach, describe, expect, it } from 'vitest'
import { render, screen } from '@testing-library/react'
import { MemoryRouter, Route, Routes } from 'react-router-dom'
import { authToken } from '@/shared/api/auth-token'
import { AuthGate } from './auth-gate'

/**
 * AuthGate is the fail-closed session gate: without a token it must redirect to
 * /login and never render the protected tree. This is a security regression the
 * gate itself had no test for — pin both directions so a refactor can't quietly
 * open it.
 */
function renderGated() {
  return render(
    <MemoryRouter initialEntries={['/protected']}>
      <Routes>
        <Route path="/login" element={<div>login-page</div>} />
        <Route
          path="/protected"
          element={
            <AuthGate>
              <div>protected-content</div>
            </AuthGate>
          }
        />
      </Routes>
    </MemoryRouter>,
  )
}

describe('AuthGate', () => {
  beforeEach(() => {
    authToken.clear()
  })
  afterEach(() => {
    authToken.clear()
  })

  it('renders the protected content when a token is present', () => {
    authToken.set('test.jwt.token')
    renderGated()

    expect(screen.getByText('protected-content')).toBeInTheDocument()
    expect(screen.queryByText('login-page')).not.toBeInTheDocument()
  })

  it('fails closed to /login when no token is set', () => {
    renderGated()

    expect(screen.getByText('login-page')).toBeInTheDocument()
    expect(screen.queryByText('protected-content')).not.toBeInTheDocument()
  })

  it('fails closed after the token is cleared (sign-out)', () => {
    authToken.set('test.jwt.token')
    authToken.clear()
    renderGated()

    expect(screen.getByText('login-page')).toBeInTheDocument()
    expect(screen.queryByText('protected-content')).not.toBeInTheDocument()
  })
})
