import { afterAll, afterEach, beforeAll, beforeEach, describe, expect, it } from 'vitest'
import { screen } from '@testing-library/react'
import { mswServer } from '@tests/msw/server'
import { renderWithRouterAndProviders } from '@tests/render/render-with-providers'
import { UserDetailView } from './UserDetailView'

describe('UserDetailView', () => {
  beforeAll(() => {
    mswServer.listen()
  })
  afterEach(() => {
    mswServer.resetHandlers()
  })
  afterAll(() => {
    mswServer.close()
  })
  beforeEach(() => {
    localStorage.setItem('nene-payout-locale', 'en')
  })

  it('renders the user fields and actions after loading', async () => {
    renderWithRouterAndProviders(<UserDetailView userId="01USER0000000000000000001" />)

    expect(await screen.findByText('admin@example.com')).toBeInTheDocument()
    expect(screen.getByText('Admin')).toBeInTheDocument()
    expect(screen.getByRole('link', { name: 'Edit' })).toBeInTheDocument()
    expect(screen.getByRole('button', { name: 'Deactivate' })).toBeInTheDocument()
  })
})
