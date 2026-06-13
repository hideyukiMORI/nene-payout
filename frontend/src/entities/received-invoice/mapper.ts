import type {
  CreateReceivedInvoiceDto,
  ReceivedInvoiceDto,
  ReceivedInvoiceListDto,
  TaxBreakdownItemDto,
  UpdateReceivedInvoiceDto,
} from './api-types'
import { toReceivedInvoiceId } from './ids'
import type {
  CreateReceivedInvoiceInput,
  ReceivedInvoice,
  ReceivedInvoiceList,
  TaxBreakdownItem,
  UpdateReceivedInvoiceInput,
} from './model'

function mapTaxBreakdownItem(dto: TaxBreakdownItemDto): TaxBreakdownItem {
  return {
    taxRateBps: dto.tax_rate_bps,
    taxableAmount: dto.taxable_amount,
    taxAmount: dto.tax_amount,
  }
}

function mapTaxBreakdownItemToDto(item: TaxBreakdownItem): TaxBreakdownItemDto {
  return {
    tax_rate_bps: item.taxRateBps,
    taxable_amount: item.taxableAmount,
    tax_amount: item.taxAmount,
  }
}

export function mapReceivedInvoiceDtoToModel(dto: ReceivedInvoiceDto): ReceivedInvoice {
  return {
    id: toReceivedInvoiceId(dto.id),
    organizationId: dto.organization_id,
    vendorId: dto.vendor_id,
    amount: dto.amount,
    dueDate: dto.due_date,
    status: dto.status,
    registrationNumber: dto.registration_number ?? null,
    taxBreakdown: (dto.tax_breakdown ?? []).map(mapTaxBreakdownItem),
    vaultDocumentUrl: dto.vault_document_url ?? null,
  }
}

export function mapReceivedInvoiceListDtoToModel(dto: ReceivedInvoiceListDto): ReceivedInvoiceList {
  return {
    items: dto.items.map(mapReceivedInvoiceDtoToModel),
    limit: dto.limit,
    offset: dto.offset,
    total: dto.total ?? null,
  }
}

export function mapCreateReceivedInvoiceInputToDto(
  input: CreateReceivedInvoiceInput,
): CreateReceivedInvoiceDto {
  return {
    vendor_id: input.vendorId,
    amount: input.amount,
    due_date: input.dueDate,
    registration_number: input.registrationNumber,
    tax_breakdown: input.taxBreakdown.map(mapTaxBreakdownItemToDto),
    vault_document_url: input.vaultDocumentUrl,
  }
}

export function mapUpdateReceivedInvoiceInputToDto(
  input: UpdateReceivedInvoiceInput,
): UpdateReceivedInvoiceDto {
  return mapCreateReceivedInvoiceInputToDto(input)
}
