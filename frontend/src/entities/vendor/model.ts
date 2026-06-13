import type { VendorId } from './ids'

export interface Vendor {
  id: VendorId
  organizationId: string
  name: string
  bankCode: string
  branchCode: string
  accountType: string
  accountNumber: string
  accountName: string
  registrationNumber: string | null
}

export interface VendorList {
  items: Vendor[]
  limit: number
  offset: number
  total: number | null
}

export interface CreateVendorInput {
  name: string
  bankCode: string
  branchCode: string
  accountType: string
  accountNumber: string
  accountName: string
  registrationNumber: string | null
}

export type UpdateVendorInput = CreateVendorInput
