import { afterAll, afterEach, beforeAll, beforeEach, describe, expect, it } from 'vitest'
import { fireEvent, screen, waitFor } from '@testing-library/react'
import { renderWithRouterAndProviders } from '@tests/render/render-with-providers'
import { mswServer } from '@tests/msw/server'
import {
  invalidCredentialsHandlers,
  sessionHandlers,
  TEST_TOKEN,
} from '@tests/msw/handlers/session'
import { SignInPanel } from './SignInPanel'

const TOKEN_KEY = 'nene_payout_token'

function fillAndSubmit() {
  fireEvent.change(screen.getByLabelText('Email address'), {
    target: { value: 'admin@example.com' },
  })
  fireEvent.change(screen.getByLabelText('Password'), { target: { value: 'secret' } })
  fireEvent.click(screen.getByRole('button', { name: 'Sign in' }))
}

describe('SignInPanel', () => {
  beforeAll(() => {
    mswServer.listen()
  })
  beforeEach(() => {
    localStorage.setItem('nene-payout-locale', 'en')
    sessionStorage.removeItem(TOKEN_KEY)
  })
  afterEach(() => {
    mswServer.resetHandlers()
  })
  afterAll(() => {
    mswServer.close()
  })

  it('stores the bearer token on a successful sign-in', async () => {
    mswServer.use(...sessionHandlers)
    renderWithRouterAndProviders(<SignInPanel />)

    fillAndSubmit()

    await waitFor(() => {
      expect(sessionStorage.getItem(TOKEN_KEY)).toBe(TEST_TOKEN)
    })
    expect(localStorage.getItem(TOKEN_KEY)).toBeNull()
  })

  it('shows the failure message and stores no token on invalid credentials', async () => {
    mswServer.use(...invalidCredentialsHandlers)
    renderWithRouterAndProviders(<SignInPanel />)

    fillAndSubmit()

    expect(await screen.findByText('Invalid email or password.')).toBeInTheDocument()
    expect(sessionStorage.getItem(TOKEN_KEY)).toBeNull()
    expect(localStorage.getItem(TOKEN_KEY)).toBeNull()
  })
})
