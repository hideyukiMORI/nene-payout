import { beforeEach, describe, expect, it, vi } from 'vitest'
import { fireEvent, screen } from '@testing-library/react'
import { renderWithProviders } from '@tests/render/render-with-providers'
import { UploadInvoicePdfForm } from './UploadInvoicePdfForm'

function pdf(name = 'invoice.pdf'): File {
  return new File(['%PDF-1.4'], name, { type: 'application/pdf' })
}

function renderForm(onSubmit: (file: File) => void) {
  return renderWithProviders(
    <UploadInvoicePdfForm
      submitting={false}
      submitError={false}
      onSubmit={onSubmit}
      onCancel={vi.fn()}
    />,
  )
}

describe('UploadInvoicePdfForm', () => {
  beforeEach(() => {
    localStorage.setItem('nene-payout-locale', 'en')
  })

  it('requires a file before submitting', () => {
    const onSubmit = vi.fn()
    renderForm(onSubmit)

    fireEvent.click(screen.getByRole('button', { name: 'Upload' }))

    expect(screen.getByText('Please choose a PDF file.')).toBeInTheDocument()
    expect(onSubmit).not.toHaveBeenCalled()
  })

  it('rejects a non-PDF file', () => {
    const onSubmit = vi.fn()
    renderForm(onSubmit)

    const notPdf = new File(['x'], 'image.png', { type: 'image/png' })
    fireEvent.change(screen.getByLabelText('PDF file'), { target: { files: [notPdf] } })
    fireEvent.click(screen.getByRole('button', { name: 'Upload' }))

    expect(screen.getByText('The file must be a PDF.')).toBeInTheDocument()
    expect(onSubmit).not.toHaveBeenCalled()
  })

  it('submits a chosen PDF file', () => {
    const onSubmit = vi.fn()
    renderForm(onSubmit)

    const file = pdf()
    fireEvent.change(screen.getByLabelText('PDF file'), { target: { files: [file] } })
    fireEvent.click(screen.getByRole('button', { name: 'Upload' }))

    expect(onSubmit).toHaveBeenCalledTimes(1)
    expect(onSubmit).toHaveBeenCalledWith(file)
  })
})
