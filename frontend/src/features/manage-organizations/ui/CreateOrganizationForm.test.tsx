import { afterAll, afterEach, beforeAll, beforeEach, describe, expect, it } from 'vitest'
import { fireEvent, screen } from '@testing-library/react'
import { mswServer } from '@tests/msw/server'
import { conflictCreateOrganizationHandlers } from '@tests/msw/handlers/organization'
import { renderWithRouterAndProviders } from '@tests/render/render-with-providers'
import { CreateOrganizationForm } from './CreateOrganizationForm'

describe('CreateOrganizationForm', () => {
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

  it('shows validation errors and does not submit when empty', async () => {
    renderWithRouterAndProviders(<CreateOrganizationForm />)

    fireEvent.click(screen.getByRole('button', { name: 'Create' }))

    expect(await screen.findByText('Slug is required.')).toBeInTheDocument()
    expect(screen.getByText('Name is required.')).toBeInTheDocument()
  })

  it('surfaces a conflict message when the slug is taken', async () => {
    mswServer.use(...conflictCreateOrganizationHandlers)
    renderWithRouterAndProviders(<CreateOrganizationForm />)

    fireEvent.change(screen.getByLabelText('Slug'), { target: { value: 'taken' } })
    fireEvent.change(screen.getByLabelText('Name'), { target: { value: 'Taken Co.' } })
    fireEvent.click(screen.getByRole('button', { name: 'Create' }))

    expect(
      await screen.findByText('That slug or custom domain is already in use.'),
    ).toBeInTheDocument()
  })
})
