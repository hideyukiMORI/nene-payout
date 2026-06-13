export interface VendorDto {
  id: string
  organization_id: string
  name: string
  bank_code: string
  branch_code: string
  account_type: string
  account_number: string
  account_name: string
  registration_number: string | null
  created_at: string
  updated_at: string
}

export interface VendorListDto {
  items: VendorDto[]
  limit: number
  offset: number
  total?: number
}

export interface CreateVendorDto {
  name: string
  bank_code: string
  branch_code: string
  account_type: string
  account_number: string
  account_name: string
  registration_number?: string | null
}

export type UpdateVendorDto = CreateVendorDto
