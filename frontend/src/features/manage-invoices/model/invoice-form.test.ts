import { describe, it, expect } from 'vitest'
import type { ReceivedInvoice } from '@/entities/received-invoice'
import { toReceivedInvoiceId } from '@/entities/received-invoice'
import {
  formValuesToCreateInput,
  invoiceFormSchema,
  invoiceToFormValues,
  type InvoiceFormValues,
} from './invoice-form'

const VALID: InvoiceFormValues = {
  vendorId: '01VENDOR000000000000000001',
  amount: '110000',
  dueDate: '2026-07-31',
  registrationNumber: 'T1234567890123',
  vaultDocumentUrl: '',
  taxBreakdown: [{ taxRateBps: '1000', taxableAmount: '100000', taxAmount: '10000' }],
}

function firstError(values: unknown): string | undefined {
  const result = invoiceFormSchema.safeParse(values)
  return result.success ? undefined : result.error.issues[0]?.message
}

describe('invoiceFormSchema', () => {
  it('accepts a fully valid form', () => {
    expect(invoiceFormSchema.safeParse(VALID).success).toBe(true)
  })

  it('accepts an empty tax breakdown', () => {
    expect(invoiceFormSchema.safeParse({ ...VALID, taxBreakdown: [] }).success).toBe(true)
  })

  it('accepts an empty registration number', () => {
    expect(invoiceFormSchema.safeParse({ ...VALID, registrationNumber: '' }).success).toBe(true)
  })

  it('rejects a missing vendor', () => {
    expect(firstError({ ...VALID, vendorId: '' })).toBe(
      'admin.receivedInvoices.form.error.vendorRequired',
    )
  })

  it('rejects a zero or non-numeric amount', () => {
    expect(firstError({ ...VALID, amount: '0' })).toBe('admin.receivedInvoices.form.error.amount')
    expect(firstError({ ...VALID, amount: 'abc' })).toBe('admin.receivedInvoices.form.error.amount')
  })

  it('converts the amount string to an integer in the create input', () => {
    expect(formValuesToCreateInput({ ...VALID, amount: '5000' }).amount).toBe(5000)
  })

  it('rejects an impossible due date', () => {
    expect(firstError({ ...VALID, dueDate: '2026-02-30' })).toBe(
      'admin.receivedInvoices.form.error.dueDate',
    )
  })

  it('rejects a malformed registration number', () => {
    expect(firstError({ ...VALID, registrationNumber: 'T123' })).toBe(
      'admin.receivedInvoices.form.error.registrationNumber',
    )
  })

  it('rejects an unsupported tax rate', () => {
    expect(
      invoiceFormSchema.safeParse({
        ...VALID,
        taxBreakdown: [{ taxRateBps: '500', taxableAmount: '1000', taxAmount: '50' }],
      }).success,
    ).toBe(false)
  })

  it('rejects a negative tax amount', () => {
    expect(
      firstError({
        ...VALID,
        taxBreakdown: [{ taxRateBps: '1000', taxableAmount: '-1', taxAmount: '0' }],
      }),
    ).toBe('admin.receivedInvoices.form.error.taxAmount')
  })
})

describe('formValuesToCreateInput', () => {
  it('maps empty optional strings to null', () => {
    const input = formValuesToCreateInput({
      ...VALID,
      registrationNumber: '',
      vaultDocumentUrl: '',
    })
    expect(input.registrationNumber).toBeNull()
    expect(input.vaultDocumentUrl).toBeNull()
  })

  it('maps the tax breakdown to entity items', () => {
    const input = formValuesToCreateInput(VALID)
    expect(input.taxBreakdown).toEqual([
      { taxRateBps: 1000, taxableAmount: 100000, taxAmount: 10000 },
    ])
  })
})

describe('invoiceToFormValues', () => {
  const invoice: ReceivedInvoice = {
    id: toReceivedInvoiceId('01INV0000000000000000000001'),
    organizationId: '01ORG00000000000000000001',
    vendorId: '01VENDOR000000000000000001',
    amount: 5000,
    dueDate: '2026-08-01',
    status: 'pending',
    registrationNumber: null,
    taxBreakdown: [],
    vaultDocumentUrl: null,
  }

  it('maps a null registration number and vault url to empty strings', () => {
    const values = invoiceToFormValues(invoice)
    expect(values.registrationNumber).toBe('')
    expect(values.vaultDocumentUrl).toBe('')
    expect(values.taxBreakdown).toEqual([])
  })
})
