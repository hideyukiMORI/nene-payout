import { afterAll, afterEach, beforeAll, beforeEach, describe, expect, it } from 'vitest'
import { screen } from '@testing-library/react'
import { mswServer } from '../../../../tests/msw/server'
import { renderWithRouterAndProviders } from '../../../../tests/render/render-with-providers'
import { OrganizationDetailView } from './OrganizationDetailView'

describe('OrganizationDetailView', () => {
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

  it('renders the organization fields and actions after loading', async () => {
    renderWithRouterAndProviders(
      <OrganizationDetailView organizationId="01ORG00000000000000000001" />,
    )

    expect(await screen.findByText('Acme 株式会社')).toBeInTheDocument()
    expect(screen.getByText('acme')).toBeInTheDocument()
    expect(screen.getByRole('link', { name: 'Edit' })).toBeInTheDocument()
    expect(screen.getByRole('button', { name: 'Deactivate' })).toBeInTheDocument()
  })
})
