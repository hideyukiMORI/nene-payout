import { afterAll, afterEach, beforeAll, beforeEach, describe, expect, it } from 'vitest'
import { fireEvent, screen, waitFor } from '@testing-library/react'
import { mswServer } from '../../../../tests/msw/server'
import { errorOrganizationHandlers } from '../../../../tests/msw/handlers/organization'
import { renderWithRouterAndProviders } from '../../../../tests/render/render-with-providers'
import { OrganizationSettingsForm } from './OrganizationSettingsForm'

describe('OrganizationSettingsForm', () => {
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

  it('loads the organization and shows the name plus read-only fields', async () => {
    renderWithRouterAndProviders(<OrganizationSettingsForm />)

    expect(await screen.findByDisplayValue('Acme 株式会社')).toBeInTheDocument()
    expect(screen.getByText('acme')).toBeInTheDocument()
    expect(screen.getByText('pay.acme.example')).toBeInTheDocument()
  })

  it('saves an edited name and shows a confirmation', async () => {
    renderWithRouterAndProviders(<OrganizationSettingsForm />)

    const input = await screen.findByLabelText('Organization name')
    fireEvent.change(input, { target: { value: 'New Name' } })
    fireEvent.click(screen.getByRole('button', { name: 'Save changes' }))

    expect(await screen.findByText('Organization settings saved.')).toBeInTheDocument()
  })

  it('shows an error state when the organization fails to load', async () => {
    mswServer.use(...errorOrganizationHandlers)
    renderWithRouterAndProviders(<OrganizationSettingsForm />)

    await waitFor(() => {
      expect(screen.getByText('Could not load data.')).toBeInTheDocument()
    })
  })
})
