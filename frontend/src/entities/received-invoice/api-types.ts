export type ReceivedInvoiceStatusDto = 'pending' | 'processing' | 'paid' | 'failed' | 'voided'

export interface TaxBreakdownItemDto {
  tax_rate_bps: number
  taxable_amount: number
  tax_amount: number
}

export interface ReceivedInvoiceDto {
  id: string
  organization_id: string
  vendor_id: string
  amount: number
  due_date: string
  status: ReceivedInvoiceStatusDto
  registration_number?: string | null
  tax_breakdown?: TaxBreakdownItemDto[]
  vault_document_url?: string | null
  created_at: string
  updated_at: string
}

export interface ReceivedInvoiceListDto {
  items: ReceivedInvoiceDto[]
  limit: number
  offset: number
  total?: number
}

export interface CreateReceivedInvoiceDto {
  vendor_id: string
  amount: number
  due_date: string
  registration_number?: string | null
  tax_breakdown?: TaxBreakdownItemDto[]
  vault_document_url?: string | null
}

export type UpdateReceivedInvoiceDto = CreateReceivedInvoiceDto
