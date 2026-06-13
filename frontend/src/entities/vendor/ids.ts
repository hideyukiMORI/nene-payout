declare const vendorIdBrand: unique symbol

export type VendorId = string & { readonly [vendorIdBrand]: never }

export function toVendorId(value: string): VendorId {
  return value as VendorId
}
