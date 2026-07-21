import { beforeEach, describe, expect, it, vi } from 'vitest'
import { fireEvent, screen, waitFor } from '@testing-library/react'
import { renderWithProviders } from '@tests/render/render-with-providers'
import { InitiatePaymentForm } from './InitiatePaymentForm'

const RETURN_URL = 'https://app.example/received-invoices'

function renderForm(onSubmit: (input: unknown) => void) {
  return renderWithProviders(
    <InitiatePaymentForm
      returnUrl={RETURN_URL}
      submitting={false}
      submitError={false}
      onSubmit={onSubmit}
      onCancel={vi.fn()}
    />,
  )
}

describe('InitiatePaymentForm', () => {
  beforeEach(() => {
    localStorage.setItem('nene-payout-locale', 'en')
  })

  it('submits the default gateway with the return url', async () => {
    const onSubmit = vi.fn()
    renderForm(onSubmit)

    fireEvent.click(screen.getByRole('button', { name: 'Pay by card' }))

    await waitFor(() => {
      expect(onSubmit).toHaveBeenCalledWith({ gateway: 'stripe', returnUrl: RETURN_URL })
    })
  })

  it('submits the chosen gateway', async () => {
    const onSubmit = vi.fn()
    renderForm(onSubmit)

    fireEvent.change(screen.getByLabelText('Gateway'), { target: { value: 'gmo_pg' } })
    fireEvent.click(screen.getByRole('button', { name: 'Pay by card' }))

    await waitFor(() => {
      expect(onSubmit).toHaveBeenCalledWith({ gateway: 'gmo_pg', returnUrl: RETURN_URL })
    })
  })
})
