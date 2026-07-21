import { afterAll, afterEach, beforeAll, beforeEach, describe, expect, it } from 'vitest'
import { fireEvent, screen } from '@testing-library/react'
import { mswServer } from '@tests/msw/server'
import { renderWithRouterAndProviders } from '@tests/render/render-with-providers'
import { InviteUserForm } from './InviteUserForm'

describe('InviteUserForm', () => {
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

  it('offers only assignable roles (no superadmin)', () => {
    renderWithRouterAndProviders(<InviteUserForm />)

    const options = screen.getAllByRole('option').map((option) => option.textContent)
    expect(options).toEqual(['Admin', 'Operator'])
  })

  it('shows a validation error and does not submit an empty email', async () => {
    renderWithRouterAndProviders(<InviteUserForm />)

    fireEvent.click(screen.getByRole('button', { name: 'Invite user' }))

    expect(await screen.findByText('Email is required.')).toBeInTheDocument()
  })

  it('rejects a malformed email', async () => {
    renderWithRouterAndProviders(<InviteUserForm />)

    fireEvent.change(screen.getByLabelText('Email'), { target: { value: 'nope' } })
    fireEvent.click(screen.getByRole('button', { name: 'Invite user' }))

    expect(await screen.findByText('Enter a valid email address.')).toBeInTheDocument()
  })
})
