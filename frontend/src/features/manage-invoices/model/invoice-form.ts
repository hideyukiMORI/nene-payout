import { z } from 'zod'
import type {
  CreateReceivedInvoiceInput,
  ReceivedInvoice,
  TaxBreakdownItem,
} from '@/entities/received-invoice'
import type { MessageKey } from '@/shared/i18n'

const REGISTRATION_NUMBER_PATTERN = /^T[0-9]{13}$/
const ISO_DATE_PATTERN = /^\d{4}-\d{2}-\d{2}$/
const POSITIVE_INT_PATTERN = /^[1-9][0-9]*$/
const NON_NEGATIVE_INT_PATTERN = /^[0-9]+$/

/** Allowed Japanese consumption-tax rates in basis points (10% / 8%), as the
 * string values used by the rate <select>. */
export const TAX_RATE_BPS_VALUES = ['1000', '800'] as const

/** True when the value is a real calendar date in YYYY-MM-DD (round-trips). */
function isRealIsoDate(value: string): boolean {
  if (!ISO_DATE_PATTERN.test(value)) {
    return false
  }
  const date = new Date(`${value}T00:00:00Z`)
  return !Number.isNaN(date.getTime()) && date.toISOString().slice(0, 10) === value
}

const taxBreakdownItemSchema = z.object({
  taxRateBps: z.enum(TAX_RATE_BPS_VALUES),
  taxableAmount: z
    .string()
    .regex(
      NON_NEGATIVE_INT_PATTERN,
      'admin.receivedInvoices.form.error.taxAmount' satisfies MessageKey,
    ),
  taxAmount: z
    .string()
    .regex(
      NON_NEGATIVE_INT_PATTERN,
      'admin.receivedInvoices.form.error.taxAmount' satisfies MessageKey,
    ),
})

/**
 * Received-invoice form schema. Rules mirror the backend
 * `ReceivedInvoiceInputMapper` exactly. All fields are strings (form inputs) and
 * are converted to integers in `formValuesToCreateInput`. Error messages are
 * i18n keys resolved by the view. Payout records the tax breakdown but never
 * computes it (ADR 0014).
 */
export const invoiceFormSchema = z.object({
  vendorId: z
    .string()
    .trim()
    .min(1, 'admin.receivedInvoices.form.error.vendorRequired' satisfies MessageKey),
  amount: z
    .string()
    .regex(POSITIVE_INT_PATTERN, 'admin.receivedInvoices.form.error.amount' satisfies MessageKey),
  dueDate: z
    .string()
    .refine(isRealIsoDate, 'admin.receivedInvoices.form.error.dueDate' satisfies MessageKey),
  registrationNumber: z.union([
    z.literal(''),
    z
      .string()
      .regex(
        REGISTRATION_NUMBER_PATTERN,
        'admin.receivedInvoices.form.error.registrationNumber' satisfies MessageKey,
      ),
  ]),
  vaultDocumentUrl: z.string(),
  taxBreakdown: z.array(taxBreakdownItemSchema),
})

export type InvoiceFormValues = z.infer<typeof invoiceFormSchema>

export const EMPTY_INVOICE_FORM_VALUES: InvoiceFormValues = {
  vendorId: '',
  amount: '',
  dueDate: '',
  registrationNumber: '',
  vaultDocumentUrl: '',
  taxBreakdown: [],
}

function mapTaxBreakdownItem(item: InvoiceFormValues['taxBreakdown'][number]): TaxBreakdownItem {
  return {
    taxRateBps: Number(item.taxRateBps),
    taxableAmount: Number(item.taxableAmount),
    taxAmount: Number(item.taxAmount),
  }
}

function taxRateToValue(taxRateBps: number): (typeof TAX_RATE_BPS_VALUES)[number] {
  return taxRateBps === 800 ? '800' : '1000'
}

/** Builds form defaults from an existing invoice (edit mode). */
export function invoiceToFormValues(invoice: ReceivedInvoice): InvoiceFormValues {
  return {
    vendorId: invoice.vendorId,
    amount: String(invoice.amount),
    dueDate: invoice.dueDate,
    registrationNumber: invoice.registrationNumber ?? '',
    vaultDocumentUrl: invoice.vaultDocumentUrl ?? '',
    taxBreakdown: invoice.taxBreakdown.map((item) => ({
      taxRateBps: taxRateToValue(item.taxRateBps),
      taxableAmount: String(item.taxableAmount),
      taxAmount: String(item.taxAmount),
    })),
  }
}

/** Maps validated form values to the entity create/update input. */
export function formValuesToCreateInput(values: InvoiceFormValues): CreateReceivedInvoiceInput {
  return {
    vendorId: values.vendorId,
    amount: Number(values.amount),
    dueDate: values.dueDate,
    registrationNumber: values.registrationNumber === '' ? null : values.registrationNumber,
    vaultDocumentUrl: values.vaultDocumentUrl === '' ? null : values.vaultDocumentUrl,
    taxBreakdown: values.taxBreakdown.map(mapTaxBreakdownItem),
  }
}
