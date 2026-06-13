import { describe, it, expect } from 'vitest'
import { PDF_MAX_BYTES, validatePdfFile } from './pdf-file'

function fileOfType(type: string, size = 1024): File {
  const file = new File(['x'], 'invoice.pdf', { type })
  Object.defineProperty(file, 'size', { value: size })
  return file
}

describe('validatePdfFile', () => {
  it('requires a file', () => {
    expect(validatePdfFile(null)).toBe('admin.receivedInvoices.pdf.error.required')
  })

  it('rejects a non-PDF media type', () => {
    expect(validatePdfFile(fileOfType('image/png'))).toBe('admin.receivedInvoices.pdf.error.type')
  })

  it('rejects a file over the size cap', () => {
    expect(validatePdfFile(fileOfType('application/pdf', PDF_MAX_BYTES + 1))).toBe(
      'admin.receivedInvoices.pdf.error.tooLarge',
    )
  })

  it('accepts a valid PDF within the cap', () => {
    expect(validatePdfFile(fileOfType('application/pdf', 1024))).toBeNull()
  })
})
