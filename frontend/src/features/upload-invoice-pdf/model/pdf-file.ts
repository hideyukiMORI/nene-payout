import type { MessageKey } from '@/shared/i18n'

/** Backend accepts only application/pdf (AttachReceivedInvoicePdfHandler). */
const PDF_MEDIA_TYPE = 'application/pdf'

/**
 * Client-side soft cap (UX only). The server is the real gate (PHP upload limit
 * → 413); this just gives immediate feedback for obviously oversized files.
 */
export const PDF_MAX_BYTES = 20 * 1024 * 1024

/**
 * Validates a chosen file before upload. Returns an i18n message key when
 * invalid, or null when acceptable. Mirrors the backend type check and adds a
 * generous size guard.
 */
export function validatePdfFile(file: File | null): MessageKey | null {
  if (file === null) {
    return 'admin.receivedInvoices.pdf.error.required'
  }
  if (file.type !== PDF_MEDIA_TYPE) {
    return 'admin.receivedInvoices.pdf.error.type'
  }
  if (file.size > PDF_MAX_BYTES) {
    return 'admin.receivedInvoices.pdf.error.tooLarge'
  }
  return null
}
