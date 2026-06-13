import { beforeEach, describe, expect, it, vi } from 'vitest'
import { fireEvent, screen, waitFor } from '@testing-library/react'
import { renderWithProviders } from '../../../../tests/render/render-with-providers'
import { InvoiceForm } from './InvoiceForm'

const VENDORS = [
  { id: '01VENDOR000000000000000001', name: '仕入先A' },
  { id: '01VENDOR000000000000000002', name: '仕入先B' },
]

function renderForm(onSubmit: (input: unknown) => void) {
  return renderWithProviders(
    <InvoiceForm
      vendors={VENDORS}
      submitLabel="Create"
      submitting={false}
      submitError={false}
      onSubmit={onSubmit}
      onCancel={vi.fn()}
    />,
  )
}

describe('InvoiceForm', () => {
  beforeEach(() => {
    localStorage.setItem('nene-payout-locale', 'en')
  })

  it('shows validation errors and does not submit when empty', async () => {
    const onSubmit = vi.fn()
    renderForm(onSubmit)

    fireEvent.click(screen.getByRole('button', { name: 'Create' }))

    expect(await screen.findByText('Vendor is required.')).toBeInTheDocument()
    expect(screen.getByText('Amount must be a positive integer.')).toBeInTheDocument()
    expect(onSubmit).not.toHaveBeenCalled()
  })

  it('submits a mapped create input including a tax line', async () => {
    const onSubmit = vi.fn()
    renderForm(onSubmit)

    fireEvent.change(screen.getByLabelText('Vendor'), {
      target: { value: '01VENDOR000000000000000001' },
    })
    fireEvent.change(screen.getByLabelText('Amount'), { target: { value: '8800' } })
    fireEvent.change(screen.getByLabelText('Due date'), { target: { value: '2026-09-30' } })

    fireEvent.click(screen.getByRole('button', { name: 'Add tax line' }))
    fireEvent.change(screen.getByLabelText('Taxable amount'), { target: { value: '8000' } })
    fireEvent.change(screen.getByLabelText('Tax amount'), { target: { value: '800' } })

    fireEvent.click(screen.getByRole('button', { name: 'Create' }))

    await waitFor(() => {
      expect(onSubmit).toHaveBeenCalledTimes(1)
    })
    expect(onSubmit).toHaveBeenCalledWith({
      vendorId: '01VENDOR000000000000000001',
      amount: 8800,
      dueDate: '2026-09-30',
      registrationNumber: null,
      vaultDocumentUrl: null,
      taxBreakdown: [{ taxRateBps: 1000, taxableAmount: 8000, taxAmount: 800 }],
    })
  })
})
