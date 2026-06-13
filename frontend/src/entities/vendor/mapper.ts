import type { CreateVendorDto, UpdateVendorDto, VendorDto, VendorListDto } from './api-types'
import { toVendorId } from './ids'
import type { CreateVendorInput, UpdateVendorInput, Vendor, VendorList } from './model'

export function mapVendorDtoToModel(dto: VendorDto): Vendor {
  return {
    id: toVendorId(dto.id),
    organizationId: dto.organization_id,
    name: dto.name,
    bankCode: dto.bank_code,
    branchCode: dto.branch_code,
    accountType: dto.account_type,
    accountNumber: dto.account_number,
    accountName: dto.account_name,
    registrationNumber: dto.registration_number,
  }
}

export function mapVendorListDtoToModel(dto: VendorListDto): VendorList {
  return {
    items: dto.items.map(mapVendorDtoToModel),
    limit: dto.limit,
    offset: dto.offset,
    total: dto.total ?? null,
  }
}

export function mapCreateVendorInputToDto(input: CreateVendorInput): CreateVendorDto {
  return {
    name: input.name,
    bank_code: input.bankCode,
    branch_code: input.branchCode,
    account_type: input.accountType,
    account_number: input.accountNumber,
    account_name: input.accountName,
    registration_number: input.registrationNumber,
  }
}

export function mapUpdateVendorInputToDto(input: UpdateVendorInput): UpdateVendorDto {
  return mapCreateVendorInputToDto(input)
}
