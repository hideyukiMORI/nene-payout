import type { ReceivedInvoiceId } from './ids'

export type ReceivedInvoiceStatus = 'pending' | 'processing' | 'paid' | 'failed' | 'voided'

export interface TaxBreakdownItem {
  taxRateBps: number
  taxableAmount: number
  taxAmount: number
}

export interface ReceivedInvoice {
  id: ReceivedInvoiceId
  organizationId: string
  vendorId: string
  amount: number
  dueDate: string
  status: ReceivedInvoiceStatus
  registrationNumber: string | null
  taxBreakdown: TaxBreakdownItem[]
  vaultDocumentUrl: string | null
}

export interface ReceivedInvoiceList {
  items: ReceivedInvoice[]
  limit: number
  offset: number
  total: number | null
}

export interface CreateReceivedInvoiceInput {
  vendorId: string
  amount: number
  dueDate: string
  registrationNumber: string | null
  taxBreakdown: TaxBreakdownItem[]
  vaultDocumentUrl: string | null
}

export type UpdateReceivedInvoiceInput = CreateReceivedInvoiceInput
