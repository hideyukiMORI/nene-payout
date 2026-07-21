import { beforeEach, describe, expect, it, vi } from 'vitest'
import { fireEvent, screen, waitFor } from '@testing-library/react'
import { renderWithProviders } from '@tests/render/render-with-providers'
import { LoginForm } from './LoginForm'

function renderForm(onSubmit: (credentials: unknown) => void) {
  return renderWithProviders(
    <LoginForm submitting={false} submitError={false} onSubmit={onSubmit} />,
  )
}

describe('LoginForm', () => {
  beforeEach(() => {
    localStorage.setItem('nene-payout-locale', 'en')
  })

  it('shows validation errors and does not submit when empty', async () => {
    const onSubmit = vi.fn()
    renderForm(onSubmit)

    fireEvent.click(screen.getByRole('button', { name: 'Sign in' }))

    expect(await screen.findByText('Enter your email address.')).toBeInTheDocument()
    expect(screen.getByText('Enter your password.')).toBeInTheDocument()
    expect(onSubmit).not.toHaveBeenCalled()
  })

  it('submits the entered credentials when valid', async () => {
    const onSubmit = vi.fn()
    renderForm(onSubmit)

    fireEvent.change(screen.getByLabelText('Email address'), {
      target: { value: 'admin@example.com' },
    })
    fireEvent.change(screen.getByLabelText('Password'), { target: { value: 'secret' } })

    fireEvent.click(screen.getByRole('button', { name: 'Sign in' }))

    await waitFor(() => {
      expect(onSubmit).toHaveBeenCalledTimes(1)
    })
    expect(onSubmit).toHaveBeenCalledWith({ email: 'admin@example.com', password: 'secret' })
  })

  it('renders the failure message when submitError is set', () => {
    localStorage.setItem('nene-payout-locale', 'en')
    renderWithProviders(<LoginForm submitting={false} submitError onSubmit={vi.fn()} />)

    expect(screen.getByRole('alert')).toHaveTextContent('Invalid email or password.')
  })
})
