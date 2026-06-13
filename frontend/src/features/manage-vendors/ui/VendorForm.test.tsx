import { beforeEach, describe, expect, it, vi } from 'vitest'
import { fireEvent, screen, waitFor } from '@testing-library/react'
import { renderWithProviders } from '../../../../tests/render/render-with-providers'
import { VendorForm } from './VendorForm'

function renderForm(onSubmit: (input: unknown) => void) {
  return renderWithProviders(
    <VendorForm
      submitLabel="Create"
      submitting={false}
      submitError={false}
      onSubmit={onSubmit}
      onCancel={vi.fn()}
    />,
  )
}

describe('VendorForm', () => {
  beforeEach(() => {
    localStorage.setItem('nene-payout-locale', 'en')
  })

  it('shows validation errors and does not submit when empty', async () => {
    const onSubmit = vi.fn()
    renderForm(onSubmit)

    fireEvent.click(screen.getByRole('button', { name: 'Create' }))

    expect(await screen.findByText('Name is required.')).toBeInTheDocument()
    expect(screen.getByText('Bank code must be 4 digits.')).toBeInTheDocument()
    expect(onSubmit).not.toHaveBeenCalled()
  })

  it('submits a mapped create input for valid values', async () => {
    const onSubmit = vi.fn()
    renderForm(onSubmit)

    fireEvent.change(screen.getByLabelText('Name'), { target: { value: 'Acme' } })
    fireEvent.change(screen.getByLabelText('Bank code'), { target: { value: '0001' } })
    fireEvent.change(screen.getByLabelText('Branch code'), { target: { value: '001' } })
    fireEvent.change(screen.getByLabelText('Account type'), { target: { value: '当座' } })
    fireEvent.change(screen.getByLabelText('Account number'), { target: { value: '1234567' } })
    fireEvent.change(screen.getByLabelText('Account holder (kana)'), {
      target: { value: 'アクメ' },
    })

    fireEvent.click(screen.getByRole('button', { name: 'Create' }))

    await waitFor(() => {
      expect(onSubmit).toHaveBeenCalledTimes(1)
    })
    expect(onSubmit).toHaveBeenCalledWith({
      name: 'Acme',
      bankCode: '0001',
      branchCode: '001',
      accountType: '当座',
      accountNumber: '1234567',
      accountName: 'アクメ',
      registrationNumber: null,
    })
  })
})
